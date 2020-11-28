<?php
namespace App\Database;

/**
 * Класс для взаимодействия с бд
 *
 * @package App\Database\Concrete
 */
class PushTokenDB
{
    /**
     * Код ошибки бд PDO
     *
     * @const int
     */
    public const DUPLICATE_ENTRY = 1062;

    /**
     * Код ошибки бд PDO
     *
     * @const int
     */
    public const TABLE_EXISTS = 1050;

    /**
     * Соединение с бд
     *
     * @var PDO|null
     */
    protected $db;

    /**
     * Класс для кэша с редис
     *
     * @var PushTokenRedis
     */
    protected $redis;

    /**
     * Подготовленное выражение для записи токена в БД
     *
     * @var PDOStatement|null
     */
    protected $dataInsertion;

    /**
     * Подготовленное выражение для запроса токена в БД
     * используя user_id
     *
     * @var PDOStatement|null
     */
    protected $dataRetrievalByUserId;

    /**
     * Подготовленное выражение для запроса токена в БД
     *  используя device_id
     *
     * @var PDOStatement|null
     */
    protected $dataRetrievalByDeviceId;

    /**
     * Подготовленное выражение для удаления токена из БД
     *
     * @var PDOStatement|null
     */
    protected $dataDeletion;

    /**
     * Подготовленное выражение для получения
     *  удаляемой проки из БД
     *
     * @var PDOStatement|null
     */
    protected $getRow;

    /**
     * Конструктор PushTokenDB.
     *
     * @param \PDO           $db
     * @param PushTokenRedis $redis
     */
    public function __construct(\PDO $db, PushTokenRedis $redis)
    {
        $this->db            = $db;
        $this->dataInsertion = $db->prepare("
            INSERT INTO Token (user_id, device_id, token, os, version)
            VALUES (:user_id, :device_id, :token, :os, :version)
        ");
        $this->dataRetrievalByUserId   = $db->prepare(
            "SELECT * FROM Token WHERE user_id = :user_id"
        );
        $this->dataRetrievalByDeviceId = $db->prepare(
            "SELECT * FROM Token WHERE device_id = :device_id"
        );
        $this->dataDeletion            = $db->prepare(
            "DELETE FROM Token WHERE token = :token"
        );
        $this->addUserToAnonymous      = $db->prepare("
            UPDATE Token
            SET user_id=:user_id, token=:token, os=:os, version=:version
            WHERE device_id=:device_id
        ");
        $this->getRow = $db->prepare("SELECT * FROM Token WHERE token = :token");
        $this->redis  = $redis;
    }

    /**
     * Деструктор PushTokenDB
     * Данный класс находится в глобальной памяти
     * Деспруктор выполняется в конце программы
     */
    public function __destruct()
    {
        $this->dataInsertion           = null;
        $this->dataRetrievalByUserId   = null;
        $this->dataRetrievalByDeviceId = null;
        $this->dataDeletion            = null;
        $this->db                      = null;
        $this->getRow                  = null;
    }

    /**
     * Метод для сохранения информации в бд
     *
     * @param  array      $data
     * @throws \Exception
     */
    public function insertData(array $data) : void
    {
        try {
            $this->dataInsertion->execute([
                ':user_id'   => $data['user_id'] ?? null,
                ':device_id' => $data['device_id'],
                ':token'     => $data['token'],
                ':os'        => $data['os'],
                ':version'   => $data['version'],
            ]);
        } catch (\PDOException $e) {
            if (isset($e->errorInfo[1])
                && $e->errorInfo[1] === self::DUPLICATE_ENTRY) {
                $this->updateData($data);
            }
        }

        if ($this->redis->keyExist($data)) {
            $this->redis->deleteData($data);
        }
    }

    /**
     * Метод для вынимания информации из бд
     *
     * @param  array      $data
     * @return array
     * @throws \Exception
     */
    public function retrieveData(array $data) : array
    {
        if ($this->redis->keyExist($data)) {
            return $this->redis->retrieveData($data);
        }

        $col    = key($data);
        $colVal = $data[$col];
        $result = null;

        switch ($col) {
            case "user_id":
                $result = $this->retrieveDataByUserId($colVal);
                break;
            case "device_id":
                $result = $this->retrieveDataByDeviceId($colVal);
                break;
            default:
                throw new \Exception("Unknown column");
        }

        $this->redis->insertData($data, $result);

        return $result;
    }

    /**
     * Выполняется из метода retrieveData(array $data) : array
     *
     * @param  int   $userId
     * @return array
     */
    protected function retrieveDataByUserId(int $userId) : array
    {
        $this->dataRetrievalByUserId->execute([':user_id' => $userId]);

        return $this->dataRetrievalByUserId->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     *  Выполняется из метода retrieveData(array $data) : array
     *
     * @param  string $deviceId
     * @return array
     */
    protected function retrieveDataByDeviceId(string $deviceId) : array
    {
        $this->dataRetrievalByDeviceId->execute([':device_id' => $deviceId]);

        $result = [];

        while ($row = $this->dataRetrievalByDeviceId->fetchAll(\PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }

        return $result ?? [];
    }

    /**
     * Метод для удаления информации из бд
     *
     * @param  array      $data
     * @throws \Exception
     */
    public function deleteData(array $data) : void
    {
        $this->getRow->execute([':token' => $data['token']]);
        $this->dataDeletion->execute([':token' => $data['token']]);

        if ($row = $this->getRow->fetch(\PDO::FETCH_ASSOC)) {
            $this->redis->deleteData($row);
        }
    }

    /**
     * Метод добавляет user_id если device_id уже существует
     * Так как device_id UNIQUE
     *
     * @param array $data
     */
    protected function updateData(array $data) : void
    {
        $this->addUserToAnonymous->execute([
            ':user_id'   => $data['user_id'] ?? null,
            ':device_id' => $data['device_id'],
            ':token'     => $data['token'],
            ':os'        => $data['os'],
            ':version'   => $data['version'],
        ]);
    }
}
