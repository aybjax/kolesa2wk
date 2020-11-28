<?php
namespace Queue\Message\Base;

class MessageBaseTemplate
{
    protected $connection;
    protected $channel;
    protected $queue;

    public function __construct(string $queueName)
    {

        $this->connection = new \PhpAmqpLib\Connection\AMQPConnection(
            ...$this->getCredentials()
        );
        $this->channel    = $this->connection->channel();
        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->queue = $queueName;
    }

    protected function getCredentials() : array
    {
        return [
            getenv("QUEUE_HOST_NAME"), getenv("QUEUE_PORT"),
            getenv("QUEUE_USER"), getenv("QUEUE_PASSWORD"),
        ];
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
