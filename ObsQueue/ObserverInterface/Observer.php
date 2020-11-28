<?php
namespace Queue\ObserverInterface;

interface Observer
{
    public function update() : void;
}
