<?php
namespace Queue\Message;

use PhpAmqpLib\Message\AMQPMessage;
use Queue\Message\Base\MessageBaseTemplate;

/**
 * Класс для отправки сообщений requeue
 *
 * @package Queue\Message
 */
class MessageSender extends MessageBaseTemplate
{
    /**
     * Названия очереди сообщения
     *
     * @const string
     */
    public const QUEUE_NAME = "outgoing.errors";

    /**
     * Конструктор MessageSender
     */
    public function __construct()
    {
        parent::__construct(self::QUEUE_NAME);
    }

    /**
     * Метод для отправки сообщений requeue
     *
     * @param string $data
     */
    public function send(string $data) : void
    {
        $msg = new AMQPMessage($data, []);
        $this->channel->basic_publish($msg, '', $this->queue);
    }
}
