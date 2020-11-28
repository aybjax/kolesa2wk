<?php
namespace Queue\Controller;

use App\Database\PushTokenDBFactory;
use App\Validator\Base\ValidatorBase;
use App\Validator\DeleteValidator;
use App\Validator\SaveValidator;
use Queue\Message\MessageSender;
use Valitron\Validator;

/**
 * Для соединения с бд
 *  имеет PushTokenDB => кэш redis обновляется
 *
 * @package Queue\Controller
 */
class QueueController
{
    public const ERROR_FORMAT = "Ошибка в методе %s\nданные с ошибкой: %s\nпричина: %s";

    /**
     * Для определения метода данных
     *
     * @const string
     */
    public const SAVE = "save";

    /**
     * Для определения метода данных
     *
     * @const string
     */
    public const DELETE = "delete";

    /**
     * Экземпля класса для взаимодействия с бд
     *
     * @var PushTokenDB
     */
    protected $db;

    /**
     * Экземпля класса
     *  для отправки сообщения requeue
     *
     * @var MessageSender
     */
    protected $sender;

    /**
     * Конструктор QueueController
     */
    public function __construct()
    {
        $this->db     = PushTokenDBFactory::createDatabase();
        $this->sender = new MessageSender();
    }

    /**
     * Для выполнения метода save
     *
     * @param string $msg
     */
    public function save(string $msg): void
    {
        try {
            $data = $this->prepareInput(self::SAVE, $msg);
            $this->db->insertData($data);
        } catch (\PDOException $e) {
            $this->sendError(self::SAVE, $msg, $e->getMessage());
        } catch (\Exception $e) {
            $this->sendError(self::SAVE, $msg, $e->getMessage());
        }
    }

    /**
     * Для выполнения метода delete
     *
     * @param string $msg
     */
    public function delete(string $msg): void
    {
        try {
            $data = $this->prepareInput(self::DELETE, $msg);
            $this->db->deleteData($data);
        } catch (\PDOException $e) {
            $this->sendError(self::DELETE, $msg, $e->getMessage());
        } catch (\Exception $e) {
            $this->sendError(self::DELETE, $msg, $e->getMessage());
        }
    }

    /**
     * Подготовка данных для записи
     *  генерррует исключения для невалидных данных
     *
     * @param  string     $context
     * @param  string     $msg
     * @return array
     * @throws \Exception
     */
    protected function prepareInput(string $context, string $msg) : array
    {
        $data      = json_decode($msg, true);
        $validator = null;

        if ($context === self::SAVE) {
            $validator = SaveValidator::getValidator($data);
        } elseif ($context === self::DELETE) {
            $validator = DeleteValidator::getValidator($data);
        }

        if (!$validator->validate()) {
            throw new \Exception($this->getErrors($validator));
        }

        return $data;
    }

    /**
     * Мето отправки сообщения requeue
     *
     * @param string $context
     * @param string $OriginalMsg
     * @param string $errorMsg
     */
    protected function sendError(string $context, string $OriginalMsg, string $errorMsg) : void
    {
        $result = sprintf(self::ERROR_FORMAT, $context, $OriginalMsg, $errorMsg);
        $this->sender->send($result);
    }

    /**
     * Чистка и рефактор ошибок валидации
     *
     * @param  Validator $validator
     * @return string
     */
    protected function getErrors(Validator $validator) : string
    {
        $errors = ValidatorBase::getValidationErrors($validator);

        return json_encode($errors);
    }
}
