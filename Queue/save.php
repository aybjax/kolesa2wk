<?php
namespace Queue;

use Queue\Message\SaveMessageListener;

require __DIR__ . "/../vendor/autoload.php";

echo "sleeping 20s для инициализации rabbitmq..." . PHP_EOL;
sleep(20);
echo "done sleeping..." . PHP_EOL;

$save = new SaveMessageListener();
try {
    $save->listen();
} catch (\Exception $e) {
    echo $e->getMessage();
}
