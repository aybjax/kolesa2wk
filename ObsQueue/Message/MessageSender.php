<?php
namespace Queue\Message;

use Queue\Controller\QueueController;
use Queue\ObserverInterface\Observer;

class MessageRequeueSender extends Base\MessageBaseTemplate implements Observer
{
    public $controller;

    public function addController(QueueController $controller)
    {
        $this->controller = $controller;
    }

    public function send(string $data) : void
    {
        $msg = new \PhpAmqpLib\Message\AMQPMessage($data, []);
        $this->channel->basic_publish($msg, '', $this->queue);
    }

    public function update(): void
    {
        // TODO: Implement update() method.
    }
}
