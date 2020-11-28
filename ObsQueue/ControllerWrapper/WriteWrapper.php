<?php
namespace Queue\ControllerWrapper;

use Queue\Controller\QueueController;
use Queue\ObserverInterface\Observer;

class WriteWrapper implements Observer
{
    public $controller;

    public function addController(QueueController $controller)
    {
        $this->controller = $controller;
    }

    public function update(): void
    {
        // TODO: Implement update() method.
    }
}
