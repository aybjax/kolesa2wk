<?php
namespace Queue\Message;

use Queue\Controller\QueueController;
use Queue\Message\Base\MessageBaseTemplate;

/**
 * Класс приема сообщений с Rabbitmq
 *
 * @package Queue\Message
 */
class DeleteMessageListener extends MessageBaseTemplate
{
    /**
     * Названия очереди сообщения
     *
     * @const string
     */
    public const QUEUE_NAME = "incoming.delete";

    /**
     * Для соединения с бд
     *
     * @var QueueController
     */
    public $controller;

    /**
     * Конструктор DeleteMessageListener
     */
    public function __construct()
    {
        parent::__construct(self::QUEUE_NAME);
        $this->controller = new QueueController();
    }

    /**
     * Метод приема сообщений
     *
     * @throws \ErrorException
     */
    public function listen()
    {
        $this->channel->basic_consume(
            $this->queue,
            '',
            false,
            false,
            false,
            false,
            $this->cb()
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * Callback при приеме сообщения
     *
     * @return callable
     */
    protected function cb() : callable
    {
        return function ($msg) {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            $this->controller->delete($msg->body);
        };
    }
}
