<?php
namespace Queue;

use Queue\Message\MessageListener;
use Queue\Message\MessageRequeueSender;
use Queue\Message\MessageSender;

require __DIR__ . "/../vendor/autoload.php";

$listener = new

$sender = new MessageRequeueSender("aybjax");
$sender->send("delete good");

$sender = new MessageListener("aybjax");
$sender->listen();

//receive

//echo "sleep 10s for containers to initialize..." > PHP_EOL;
//sleep(10);
//echo "starting..." > PHP_EOL;

//try {
//    $queue      = getenv('QUEUE_NAME');
//    $connection = new \PhpAmqpLib\Connection\AMQPConnection(getenv("QUEUE_HOST_NAME"), getenv("QUEUE_PORT"),
//        getenv("QUEUE_USER"), getenv("QUEUE_PASSWORD"));
//    $channel    = $connection->channel();
//    $channel->queue_declare($queue, false, true, false, false);
//
//    $msg = new \PhpAmqpLib\Message\AMQPMessage($data, []);
//    $channel->basic_publish($msg, '', $queue);
//    $channel->close();
//    $connection->close();
//
//} catch (\Exception $e) {
//    echo $e->getMessage() . PHP_EOL;
//}




//$queue      = getenv('QUEUE_NAME');
//$connection = new \PhpAmqpLib\Connection\AMQPConnection("queue", 5672, 'guest', 'guest');
//$channel    = $connection->channel();
//$channel->queue_declare($queue, false, true, false, false);
//
//function callback($msg)
//{
//    global $stop_words;
//
//    try {
//        echo $msg->body . PHP_EOL;
//        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
//    } catch (Exception $ex) {
//
//    }
//}
//
//$channel->basic_consume($queue, '', false, false, false, false, 'Queue\callback');
//
//while (count($channel->callbacks)) {
//    $channel->wait();
//}