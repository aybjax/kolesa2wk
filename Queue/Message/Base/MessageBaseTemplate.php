<?php
namespace Queue\Message\Base;

use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Шаблон для классов
 *  соединения с Rabbitmq
 *
 * @package Queue\Message\Base
 */
class MessageBaseTemplate
{
    /**
     * Экземпляр соединения
     *  через AMQP с Rabbitmq
     *
     * @var AMQPConnection
     */
    protected $connection;

    /**
     * Канал соединения на порте с Rabbitmq
     *
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * Названия соединия Queue
     *
     * @var string
     */
    protected $queue;

    /**
     * Конструктор MessageBaseTemplate
     *
     * @param string $queueName
     */
    public function __construct(string $queueName)
    {
        $this->queue      = $queueName;
        $this->connection = new AMQPConnection(
            ...$this->getCredentials()
        );
        $this->channel    = $this->connection->channel();

        $this->channel->queue_declare(
            $queueName,
            false,
            true,
            false,
            false
        );
    }

    /**
     * Возвращает аргументы для соединения с Rabbitmq
     *
     * @return array
     */
    protected function getCredentials() : array
    {
        return [
            getenv("QUEUE_HOST_NAME"),
            getenv("QUEUE_PORT"),
            getenv("QUEUE_USER"),
            getenv("QUEUE_PASSWORD"),
        ];
    }

    /**
     * Дкструктор MessageBaseTemplate
     *
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
