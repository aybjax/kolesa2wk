<?php
namespace Queue\ObserverInterface;

interface Observable
{
    public function add(Observer $observer) : void;

    public function notify() : void;
}
