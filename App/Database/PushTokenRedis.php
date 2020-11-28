<?php
namespace App\Database;

use Predis\Client;

/**
 * Класс для раьоты с бд Redis
 *
 * @package App\Database
 */
class PushTokenRedis
{
    /**
     * Текст используется как индекс в массиве
     *
     * @const string
     */
    public const DEVICE_ID_STR = 'device_id';

    /**
     * Текст используется как индекс в массиве
     *
     * @const string
     */
    public const USER_ID_STR = 'user_id';

    /**
     * Соединение с редис
     *
     * @var Client
     */
    protected $db;

    /**
     * Конструктор PushTokenRedis.
     *
     * @param Client $db
     */
    public function __construct(Client $db)
    {
        $this->db = $db;
    }

    /**
     * Добавление в хранение
     * $dataWithKey всегда одномерный массив =>
     *  используется для вынимания ключа
     *
     * @param array $dataWithKey
     * @param array $data
     */
    public function insertData(array $dataWithKey, array $data): void
    {
        $hashKey = $this->getStringKey($dataWithKey);

        $this->db->set($hashKey, \json_encode($data));
    }

    /**
     * Запрос с хранения
     *
     * @param  array      $data
     * @return array
     * @throws \Exception
     */
    public function retrieveData(array $data): array
    {
        $hashKey  = $this->getStringKey($data);
        $jsonData = $this->db->get($hashKey);

        return \json_decode($jsonData, true);
    }

    /**
     * Проеряет наличие запроса в кэше
     *
     * @param  array $data
     * @return bool
     */
    public function keyExist(array $data) : bool
    {
        $hashKey = $this->getStringKey($data);

        if ($this->db->exists($hashKey)) {
            return true;
        }

        return false;
    }

    /**
     * Удаление с хранения
     *
     * @param  array      $data
     * @throws \Exception
     */
    public function deleteData(array $data): void
    {
        $hashKey = $this->getStringKey($data);

        $this->db->del($hashKey);
    }

    /**
     * Возвращает ключ хэша user или device
     *
     * @param  array  $data
     * @return string
     */
    protected function getStringKey(array $data): string
    {
        $key = isset($data[self::USER_ID_STR])
            ? self::USER_ID_STR
            : self::DEVICE_ID_STR;

        return sprintf("%s:%s", $data[$key], $key);
    }
}
