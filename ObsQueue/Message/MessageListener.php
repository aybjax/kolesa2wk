<?php
namespace Queue\Message;

use Queue\ObserverInterface\Observable;
use Queue\ObserverInterface\Observer;

class MessageListener extends Base\MessageBaseTemplate implements Observable
{
    const ERROR_MSG = "Wrong request format";

    protected $action;
    protected $payload;
    protected $message;
    protected $observers;

    public function __construct(string $queueName)
    {
        parent::__construct($queueName);
        $this->observers = [];
    }

    protected function parseMessage($msg) : void
    {
        $this->message = $msg;
        $data          = explode(" ", $this->message->body);

        if (count($data) !== 2) {
            //prevent
            throw new \Exception(self::ERROR_MSG);
        }

        $this->action  = $data[0];
        $this->payload = $data[1];

        $this->acknowledge();
    }

    protected function cb() : callable
    {
        return function ($msg) {
            $this->parseMessage($msg);
        };
    }

    public function acknowledge()
    {
        $this->message->delivery_info['channel']->basic_ack($this->message->delivery_info['delivery_tag']);
    }

    public function listen()
    {
        $cb = $this->cb();
        $this->channel->basic_consume(
            $this->queue,
            '',
            false,
            false,
            false,
            false,
            $cb
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer . update();
        }
    }

    public function add(Observer $observer): void
    {
        $this->observers[] = $observer;
    }
}
