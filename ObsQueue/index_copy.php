<?php
namespace Queue;

require __DIR__ . "/../vendor/autoload.php";
//receive

try {
    $queue      = 'echo';
    $connection = new \PhpAmqpLib\Connection\AMQPConnection('mq', 5672, 'guest', 'guest');
    $channel    = $connection->channel();
    $channel->queue_declare($queue, false, true, false, false);

    $msgs = [
        'Hello',
        'World',
        'mac',
        'was',
        'here',
        'lorem',
        'ipsum',
    ];
    shuffle($msgs);

    foreach ($msgs as $data) {
        $msg = new \PhpAmqpLib\Message\AMQPMessage($data, [
            'delivery_mode' => 2,
            'priority'      => 1,
            'timestamp'     => time(),
            'expiration'    => strval(1000 * (strtotime('+1 day midnight') - time() - 1)),
        ]);
        $channel->basic_publish($msg, '', $queue);
        echo ' [>] "' . $data . '" sent' . PHP_EOL;
    }
    $channel->close();
    $connection->close();

} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}




$queue      = 'echo';
$connection = new \PhpAmqpLib\Connection\AMQPConnection('mq', 5672, 'guest', 'guest');
$channel    = $connection->channel();
$channel->queue_declare($queue, false, true, false, false);

global $stop_words;
$stop_words = array_slice($argv, 1);

echo ' [*] Waiting for messages. To exit press CTRL+C' . PHP_EOL;

$cb = function ($msg)
{
    global $stop_words;

    try {
        echo ' [x] Received ' . $msg->body . ' (try: ' . $msg->get('priority') . ')' . PHP_EOL;
        if ($msg->get("priority") > 3) {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            echo ' [!] Maximum retries reached at ' . $msg->get('priority') . ' retries' . PHP_EOL;
        } else {
            if (in_array($msg->body, $stop_words)) throw new Exception('Stop word detected');
            sleep(strlen($msg->body));
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            echo ' [+] Done' . PHP_EOL . PHP_EOL;

        }
//        echo $msg->body . PHP_EOL;
//        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    } catch (Exception $ex) {
        $channel = $msg->get('channel');
        $queue   = $msg->delivery_info['routing_key'];
        $new_msg = new \PhpAmqpLib\Message\AMQPMessage($msg->body, [
            'delivery_mode' => 2,
            'priority'      => 1 + $msg->get('priority'),
            'timestamp'     => time(),
            'expiration'    => strval(1000 * (strtotime('+1 day midnight') - time() - 1)),
        ]);
        $channel->basic_publish($new_msg, '', $queue);

        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        echo ' [!] ERROR: ' . $ex->getMessage() . PHP_EOL . PHP_EOL;
    }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume($queue, '', false, false, false, false, $cb);

function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);

while (count($channel->callbacks)) {
    $channel->wait();
}
/*
PhpAmqpLib\Message\AMQPMessage Object
cli         | (
    cli         |     [body] => lorem
cli         |     [body_size] => 5
cli         |     [is_truncated] =>
cli         |     [content_encoding] =>
cli         |     [delivery_info] => Array
    cli         |         (
        cli         |             [channel] => PhpAmqpLib\Channel\AMQPChannel Object
cli         |                 (
    cli         |                     [callbacks] => Array
    cli         |                         (
        cli         |                             [amq.ctag-ile9LfbndGzfMfPscCmcRA] => callback
cli         |                         )
cli         |
cli         |                     [is_open:protected] => 1
cli         |                     [default_ticket:protected] => 0
cli         |                     [active:protected] => 1
cli         |                     [alerts:protected] => Array
    cli         |                         (
        cli         |                         )
cli         |
cli         |                     [auto_decode:protected] => 1
cli         |                     [basic_return_callback:protected] =>
cli         |                     [batch_messages:protected] => Array
    cli         |                         (
        cli         |                         )
cli         |
cli         |                     [published_messages:PhpAmqpLib\Channel\AMQPChannel:private] => Array
    cli         |                         (
        cli         |                         )
cli         |
cli         |                     [next_delivery_tag:PhpAmqpLib\Channel\AMQPChannel:private] => 0
cli         |                     [ack_handler:PhpAmqpLib\Channel\AMQPChannel:private] =>
cli         |                     [nack_handler:PhpAmqpLib\Channel\AMQPChannel:private] =>
cli         |                     [publish_cache:PhpAmqpLib\Channel\AMQPChannel:private] => Array
    cli         |                         (
        cli         |                         )
cli         |
cli         |                     [publish_cache_max_size:PhpAmqpLib\Channel\AMQPChannel:private] => 100
cli         |                     [channel_rpc_timeout:PhpAmqpLib\Channel\AMQPChannel:private] => 0
cli         |                     [frame_queue:protected] => Array
    cli         |                         (
        cli         |                         )
cli         |
cli         |                     [method_queue:protected] => Array
    cli         |                         (
        cli         |                         )
cli         |
cli         |                     [constants:protected] => PhpAmqpLib\Wire\Constants091 Object
cli         |                         (
    cli         |                         )
cli         |
cli         |                     [debug:protected] => PhpAmqpLib\Helper\DebugHelper Object
cli         |                         (
    cli         |                             [debug:protected] =>
cli         |                             [debug_output:protected] => Resource id #2
cli         |                             [constants:protected] => PhpAmqpLib\Wire\Constants091 Object
cli         |                                 (
    cli         |                                 )
cli         |
cli         |                         )
cli         |
cli         |                     [connection:protected] => PhpAmqpLib\Connection\AMQPConnection Object
cli         |                         (
    cli         |                             [channels] => Array
    cli         |                                 (
        cli         |                                     [0] => PhpAmqpLib\Connection\AMQPConnection Object
cli         |  *RECURSION*
cli         |                                     [1] => PhpAmqpLib\Channel\AMQPChannel Object
cli         |  *RECURSION*
cli         |                                 )
cli         |
cli         |                             [version_major:protected] => 0
cli         |                             [version_minor:protected] => 9
cli         |                             [server_properties:protected] => Array
    cli         |                                 (
        cli         |                                     [capabilities] => Array
        cli         |                                         (
            cli         |                                             [0] => F
cli         |                                             [1] => Array
    cli         |                                                 (
        cli         |                                                     [publisher_confirms] => Array
        cli         |                                                         (
            cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [exchange_exchange_bindings] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [basic.nack] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [consumer_cancel_notify] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [connection.blocked] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [consumer_priorities] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [authentication_failure_close] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [per_consumer_qos] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                     [direct_reply_to] => Array
    cli         |                                                         (
        cli         |                                                             [0] => t
cli         |                                                             [1] => 1
cli         |                                                         )
cli         |
cli         |                                                 )
cli         |
cli         |                                         )
cli         |
cli         |                                     [cluster_name] => Array
    cli         |                                         (
        cli         |                                             [0] => S
cli         |                                             [1] => rabbit@mq
cli         |                                         )
cli         |
cli         |                                     [copyright] => Array
    cli         |                                         (
        cli         |                                             [0] => S
cli         |                                             [1] => Copyright (c) 2007-2020 VMware, Inc. or its affiliates.
cli         |                                         )
cli         |
cli         |                                     [information] => Array
    cli         |                                         (
        cli         |                                             [0] => S
cli         |                                             [1] => Licensed under the MPL 2.0. Website: https://rabbitmq.com
cli         |                                         )
cli         |
cli         |                                     [platform] => Array
    cli         |                                         (
        cli         |                                             [0] => S
cli         |                                             [1] => Erlang/OTP 23.1.4
cli         |                                         )
cli         |
cli         |                                     [product] => Array
    cli         |                                         (
        cli         |                                             [0] => S
cli         |                                             [1] => RabbitMQ
cli         |                                         )
cli         |
cli         |                                     [version] => Array
    cli         |                                         (
        cli         |                                             [0] => S
cli         |                                             [1] => 3.8.9
cli         |                                         )
cli         |
cli         |                                 )
cli         |
cli         |                             [mechanisms:protected] => Array
    cli         |                                 (
        cli         |                                     [0] => AMQPLAIN
cli         |                                     [1] => PLAIN
cli         |                                 )
cli         |
cli         |                             [locales:protected] => Array
    cli         |                                 (
        cli         |                                     [0] => en_US
cli         |                                 )
cli         |
cli         |                             [wait_tune_ok:protected] =>
cli         |                             [known_hosts:protected] =>
cli         |                             [input:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                                 (
    cli         |                                     [str:protected] =>
cli         |                                     [str_length:protected] => 0
cli         |                                     [offset:protected] => 746
cli         |                                     [bitcount:protected] => 0
cli         |                                     [timeout:protected] => 0
cli         |                                     [bits:protected] => 0
cli         |                                     [io:protected] => PhpAmqpLib\Wire\IO\StreamIO Object
cli         |                                         (
    cli         |                                             [protocol:protected] => tcp
cli         |                                             [context:protected] => Resource id #20
cli         |                                             [sock:PhpAmqpLib\Wire\IO\StreamIO:private] => Resource id #25
cli         |                                             [host:protected] => mq
cli         |                                             [port:protected] => 5672
cli         |                                             [connection_timeout:protected] => 3
cli         |                                             [read_timeout:protected] => 3
cli         |                                             [write_timeout:protected] => 3
cli         |                                             [heartbeat:protected] => 0
cli         |                                             [initial_heartbeat:protected] => 0
cli         |                                             [keepalive:protected] =>
cli         |                                             [last_read:protected] => 1606302809.6847
cli         |                                             [last_write:protected] => 1606302809.6841
cli         |                                             [last_error:protected] =>
cli         |                                             [canDispatchPcntlSignal:protected] =>
cli         |                                         )
cli         |
cli         |                                     [is64bits:protected] => 1
cli         |                                 )
cli         |
cli         |                             [vhost:protected] => /
cli         |                             [insist:protected] =>
cli         |                             [login_method:protected] => AMQPLAIN
cli         |                             [login_response:protected] => LOGINSguesPASSWORDSguest
cli         |                             [locale:protected] => en_US
cli         |                             [heartbeat:protected] => 0
cli         |                             [last_frame:protected] => 1606302809.6847
cli         |                             [channel_max:protected] => 2047
cli         |                             [frame_max:protected] => 131072
cli         |                             [construct_params:protected] => Array
    cli         |                                 (
        cli         |                                     [0] => mq
cli         |                                     [1] => 5672
cli         |                                     [2] => guest
cli         |                                     [3] => guest
cli         |                                 )
cli         |
cli         |                             [close_on_destruct:protected] => 1
cli         |                             [is_connected:protected] => 1
cli         |                             [io:protected] => PhpAmqpLib\Wire\IO\StreamIO Object
cli         |                                 (
    cli         |                                     [protocol:protected] => tcp
cli         |                                     [context:protected] => Resource id #20
cli         |                                     [sock:PhpAmqpLib\Wire\IO\StreamIO:private] => Resource id #25
cli         |                                     [host:protected] => mq
cli         |                                     [port:protected] => 5672
cli         |                                     [connection_timeout:protected] => 3
cli         |                                     [read_timeout:protected] => 3
cli         |                                     [write_timeout:protected] => 3
cli         |                                     [heartbeat:protected] => 0
cli         |                                     [initial_heartbeat:protected] => 0
cli         |                                     [keepalive:protected] =>
cli         |                                     [last_read:protected] => 1606302809.6847
cli         |                                     [last_write:protected] => 1606302809.6841
cli         |                                     [last_error:protected] =>
cli         |                                     [canDispatchPcntlSignal:protected] =>
cli         |                                 )
cli         |
cli         |                             [wait_frame_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                                 (
    cli         |                                     [str:protected] =>
cli         |                                     [str_length:protected] => 0
cli         |                                     [offset:protected] => 6
cli         |                                     [bitcount:protected] => 0
cli         |                                     [timeout:protected] => 0
cli         |                                     [bits:protected] => 0
cli         |                                     [io:protected] =>
cli         |                                     [is64bits:protected] => 1
cli         |                                 )
cli         |
cli         |                             [connection_block_handler:PhpAmqpLib\Connection\AbstractConnection:private] =>
cli         |                             [connection_unblock_handler:PhpAmqpLib\Connection\AbstractConnection:private] =>
cli         |                             [connection_timeout:protected] => 3
cli         |                             [prepare_content_cache:PhpAmqpLib\Connection\AbstractConnection:private] => Array
    cli         |                                 (
        cli         |                                 )
cli         |
cli         |                             [prepare_content_cache_max_size:PhpAmqpLib\Connection\AbstractConnection:private] => 100
cli         |                             [channel_rpc_timeout:PhpAmqpLib\Connection\AbstractConnection:private] => 0
cli         |                             [frame_queue:protected] => Array
    cli         |                                 (
        cli         |                                 )
cli         |
cli         |                             [method_queue:protected] => Array
    cli         |                                 (
        cli         |                                 )
cli         |
cli         |                             [auto_decode:protected] =>
cli         |                             [constants:protected] => PhpAmqpLib\Wire\Constants091 Object
cli         |                                 (
    cli         |                                 )
cli         |
cli         |                             [debug:protected] => PhpAmqpLib\Helper\DebugHelper Object
cli         |                                 (
    cli         |                                     [debug:protected] =>
cli         |                                     [debug_output:protected] => Resource id #2
cli         |                                     [constants:protected] => PhpAmqpLib\Wire\Constants091 Object
cli         |                                         (
    cli         |                                         )
cli         |
cli         |                                 )
cli         |
cli         |                             [connection:protected] => PhpAmqpLib\Connection\AMQPConnection Object
cli         |  *RECURSION*
cli         |                             [protocolVersion:protected] => 0.9.1
cli         |                             [maxBodySize:protected] =>
cli         |                             [protocolWriter:protected] => PhpAmqpLib\Helper\Protocol\Protocol091 Object
cli         |                                 (
    cli         |                                 )
cli         |
cli         |                             [waitHelper:protected] => PhpAmqpLib\Helper\Protocol\Wait091 Object
cli         |                                 (
    cli         |                                     [wait:protected] => Array
    cli         |                                         (
        cli         |                                             [connection.start] => 10,10
cli         |                                             [connection.start_ok] => 10,11
cli         |                                             [connection.secure] => 10,20
cli         |                                             [connection.secure_ok] => 10,21
cli         |                                             [connection.tune] => 10,30
cli         |                                             [connection.tune_ok] => 10,31
cli         |                                             [connection.open] => 10,40
cli         |                                             [connection.open_ok] => 10,41
cli         |                                             [connection.close] => 10,50
cli         |                                             [connection.close_ok] => 10,51
cli         |                                             [connection.blocked] => 10,60
cli         |                                             [connection.unblocked] => 10,61
cli         |                                             [channel.open] => 20,10
cli         |                                             [channel.open_ok] => 20,11
cli         |                                             [channel.flow] => 20,20
cli         |                                             [channel.flow_ok] => 20,21
cli         |                                             [channel.close] => 20,40
cli         |                                             [channel.close_ok] => 20,41
cli         |                                             [access.request] => 30,10
cli         |                                             [access.request_ok] => 30,11
cli         |                                             [exchange.declare] => 40,10
cli         |                                             [exchange.declare_ok] => 40,11
cli         |                                             [exchange.delete] => 40,20
cli         |                                             [exchange.delete_ok] => 40,21
cli         |                                             [exchange.bind] => 40,30
cli         |                                             [exchange.bind_ok] => 40,31
cli         |                                             [exchange.unbind] => 40,40
cli         |                                             [exchange.unbind_ok] => 40,51
cli         |                                             [queue.declare] => 50,10
cli         |                                             [queue.declare_ok] => 50,11
cli         |                                             [queue.bind] => 50,20
cli         |                                             [queue.bind_ok] => 50,21
cli         |                                             [queue.purge] => 50,30
cli         |                                             [queue.purge_ok] => 50,31
cli         |                                             [queue.delete] => 50,40
cli         |                                             [queue.delete_ok] => 50,41
cli         |                                             [queue.unbind] => 50,50
cli         |                                             [queue.unbind_ok] => 50,51
cli         |                                             [basic.qos] => 60,10
cli         |                                             [basic.qos_ok] => 60,11
cli         |                                             [basic.consume] => 60,20
cli         |                                             [basic.consume_ok] => 60,21
cli         |                                             [basic.cancel] => 60,30
cli         |                                             [basic.cancel_ok] => 60,31
cli         |                                             [basic.publish] => 60,40
cli         |                                             [basic.return] => 60,50
cli         |                                             [basic.deliver] => 60,60
cli         |                                             [basic.get] => 60,70
cli         |                                             [basic.get_ok] => 60,71
cli         |                                             [basic.get_empty] => 60,72
cli         |                                             [basic.ack] => 60,80
cli         |                                             [basic.reject] => 60,90
cli         |                                             [basic.recover_async] => 60,100
cli         |                                             [basic.recover] => 60,110
cli         |                                             [basic.recover_ok] => 60,111
cli         |                                             [basic.nack] => 60,120
cli         |                                             [tx.select] => 90,10
cli         |                                             [tx.select_ok] => 90,11
cli         |                                             [tx.commit] => 90,20
cli         |                                             [tx.commit_ok] => 90,21
cli         |                                             [tx.rollback] => 90,30
cli         |                                             [tx.rollback_ok] => 90,31
cli         |                                             [confirm.select] => 85,10
cli         |                                             [confirm.select_ok] => 85,11
cli         |                                         )
cli         |
cli         |                                 )
cli         |
cli         |                             [methodMap:protected] => PhpAmqpLib\Helper\Protocol\MethodMap091 Object
cli         |                                 (
    cli         |                                     [method_map:protected] => Array
    cli         |                                         (
        cli         |                                             [10,10] => connection_start
cli         |                                             [10,11] => connection_start_ok
cli         |                                             [10,20] => connection_secure
cli         |                                             [10,21] => connection_secure_ok
cli         |                                             [10,30] => connection_tune
cli         |                                             [10,31] => connection_tune_ok
cli         |                                             [10,40] => connection_open
cli         |                                             [10,41] => connection_open_ok
cli         |                                             [10,50] => connection_close
cli         |                                             [10,51] => connection_close_ok
cli         |                                             [10,60] => connection_blocked
cli         |                                             [10,61] => connection_unblocked
cli         |                                             [20,10] => channel_open
cli         |                                             [20,11] => channel_open_ok
cli         |                                             [20,20] => channel_flow
cli         |                                             [20,21] => channel_flow_ok
cli         |                                             [20,40] => channel_close
cli         |                                             [20,41] => channel_close_ok
cli         |                                             [30,10] => access_request
cli         |                                             [30,11] => access_request_ok
cli         |                                             [40,10] => exchange_declare
cli         |                                             [40,11] => exchange_declare_ok
cli         |                                             [40,20] => exchange_delete
cli         |                                             [40,21] => exchange_delete_ok
cli         |                                             [40,30] => exchange_bind
cli         |                                             [40,31] => exchange_bind_ok
cli         |                                             [40,40] => exchange_unbind
cli         |                                             [40,51] => exchange_unbind_ok
cli         |                                             [50,10] => queue_declare
cli         |                                             [50,11] => queue_declare_ok
cli         |                                             [50,20] => queue_bind
cli         |                                             [50,21] => queue_bind_ok
cli         |                                             [50,30] => queue_purge
cli         |                                             [50,31] => queue_purge_ok
cli         |                                             [50,40] => queue_delete
cli         |                                             [50,41] => queue_delete_ok
cli         |                                             [50,50] => queue_unbind
cli         |                                             [50,51] => queue_unbind_ok
cli         |                                             [60,10] => basic_qos
cli         |                                             [60,11] => basic_qos_ok
cli         |                                             [60,20] => basic_consume
cli         |                                             [60,21] => basic_consume_ok
cli         |                                             [60,30] => basic_cancel_from_server
cli         |                                             [60,31] => basic_cancel_ok
cli         |                                             [60,40] => basic_publish
cli         |                                             [60,50] => basic_return
cli         |                                             [60,60] => basic_deliver
cli         |                                             [60,70] => basic_get
cli         |                                             [60,71] => basic_get_ok
cli         |                                             [60,72] => basic_get_empty
cli         |                                             [60,80] => basic_ack_from_server
cli         |                                             [60,90] => basic_reject
cli         |                                             [60,100] => basic_recover_async
cli         |                                             [60,110] => basic_recover
cli         |                                             [60,111] => basic_recover_ok
cli         |                                             [60,120] => basic_nack_from_server
cli         |                                             [90,10] => tx_select
cli         |                                             [90,11] => tx_select_ok
cli         |                                             [90,20] => tx_commit
cli         |                                             [90,21] => tx_commit_ok
cli         |                                             [90,30] => tx_rollback
cli         |                                             [90,31] => tx_rollback_ok
cli         |                                             [85,10] => confirm_select
cli         |                                             [85,11] => confirm_select_ok
cli         |                                         )
cli         |
cli         |                                 )
cli         |
cli         |                             [channel_id:protected] => 0
cli         |                             [msg_property_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                                 (
    cli         |                                     [str:protected] =>
cli         |                                     [str_length:protected] => 0
cli         |                                     [offset:protected] => 0
cli         |                                     [bitcount:protected] => 0
cli         |                                     [timeout:protected] => 0
cli         |                                     [bits:protected] => 0
cli         |                                     [io:protected] =>
cli         |                                     [is64bits:protected] => 1
cli         |                                 )
cli         |
cli         |                             [wait_content_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                                 (
    cli         |                                     [str:protected] =>
cli         |                                     [str_length:protected] => 0
cli         |                                     [offset:protected] => 0
cli         |                                     [bitcount:protected] => 0
cli         |                                     [timeout:protected] => 0
cli         |                                     [bits:protected] => 0
cli         |                                     [io:protected] =>
cli         |                                     [is64bits:protected] => 1
cli         |                                 )
cli         |
cli         |                             [dispatch_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                                 (
    cli         |                                     [str:protected] =>
cli         |                                     [str_length:protected] => 0
cli         |                                     [offset:protected] => 1
cli         |                                     [bitcount:protected] => 0
cli         |                                     [timeout:protected] => 0
cli         |                                     [bits:protected] => 0
cli         |                                     [io:protected] =>
cli         |                                     [is64bits:protected] => 1
cli         |                                 )
cli         |
cli         |                         )
cli         |
cli         |                     [protocolVersion:protected] => 0.9.1
cli         |                     [maxBodySize:protected] =>
cli         |                     [protocolWriter:protected] => PhpAmqpLib\Helper\Protocol\Protocol091 Object
cli         |                         (
    cli         |                         )
cli         |
cli         |                     [waitHelper:protected] => PhpAmqpLib\Helper\Protocol\Wait091 Object
cli         |                         (
    cli         |                             [wait:protected] => Array
    cli         |                                 (
        cli         |                                     [connection.start] => 10,10
cli         |                                     [connection.start_ok] => 10,11
cli         |                                     [connection.secure] => 10,20
cli         |                                     [connection.secure_ok] => 10,21
cli         |                                     [connection.tune] => 10,30
cli         |                                     [connection.tune_ok] => 10,31
cli         |                                     [connection.open] => 10,40
cli         |                                     [connection.open_ok] => 10,41
cli         |                                     [connection.close] => 10,50
cli         |                                     [connection.close_ok] => 10,51
cli         |                                     [connection.blocked] => 10,60
cli         |                                     [connection.unblocked] => 10,61
cli         |                                     [channel.open] => 20,10
cli         |                                     [channel.open_ok] => 20,11
cli         |                                     [channel.flow] => 20,20
cli         |                                     [channel.flow_ok] => 20,21
cli         |                                     [channel.close] => 20,40
cli         |                                     [channel.close_ok] => 20,41
cli         |                                     [access.request] => 30,10
cli         |                                     [access.request_ok] => 30,11
cli         |                                     [exchange.declare] => 40,10
cli         |                                     [exchange.declare_ok] => 40,11
cli         |                                     [exchange.delete] => 40,20
cli         |                                     [exchange.delete_ok] => 40,21
cli         |                                     [exchange.bind] => 40,30
cli         |                                     [exchange.bind_ok] => 40,31
cli         |                                     [exchange.unbind] => 40,40
cli         |                                     [exchange.unbind_ok] => 40,51
cli         |                                     [queue.declare] => 50,10
cli         |                                     [queue.declare_ok] => 50,11
cli         |                                     [queue.bind] => 50,20
cli         |                                     [queue.bind_ok] => 50,21
cli         |                                     [queue.purge] => 50,30
cli         |                                     [queue.purge_ok] => 50,31
cli         |                                     [queue.delete] => 50,40
cli         |                                     [queue.delete_ok] => 50,41
cli         |                                     [queue.unbind] => 50,50
cli         |                                     [queue.unbind_ok] => 50,51
cli         |                                     [basic.qos] => 60,10
cli         |                                     [basic.qos_ok] => 60,11
cli         |                                     [basic.consume] => 60,20
cli         |                                     [basic.consume_ok] => 60,21
cli         |                                     [basic.cancel] => 60,30
cli         |                                     [basic.cancel_ok] => 60,31
cli         |                                     [basic.publish] => 60,40
cli         |                                     [basic.return] => 60,50
cli         |                                     [basic.deliver] => 60,60
cli         |                                     [basic.get] => 60,70
cli         |                                     [basic.get_ok] => 60,71
cli         |                                     [basic.get_empty] => 60,72
cli         |                                     [basic.ack] => 60,80
cli         |                                     [basic.reject] => 60,90
cli         |                                     [basic.recover_async] => 60,100
cli         |                                     [basic.recover] => 60,110
cli         |                                     [basic.recover_ok] => 60,111
cli         |                                     [basic.nack] => 60,120
cli         |                                     [tx.select] => 90,10
cli         |                                     [tx.select_ok] => 90,11
cli         |                                     [tx.commit] => 90,20
cli         |                                     [tx.commit_ok] => 90,21
cli         |                                     [tx.rollback] => 90,30
cli         |                                     [tx.rollback_ok] => 90,31
cli         |                                     [confirm.select] => 85,10
cli         |                                     [confirm.select_ok] => 85,11
cli         |                                 )
cli         |
cli         |                         )
cli         |
cli         |                     [methodMap:protected] => PhpAmqpLib\Helper\Protocol\MethodMap091 Object
cli         |                         (
    cli         |                             [method_map:protected] => Array
    cli         |                                 (
        cli         |                                     [10,10] => connection_start
cli         |                                     [10,11] => connection_start_ok
cli         |                                     [10,20] => connection_secure
cli         |                                     [10,21] => connection_secure_ok
cli         |                                     [10,30] => connection_tune
cli         |                                     [10,31] => connection_tune_ok
cli         |                                     [10,40] => connection_open
cli         |                                     [10,41] => connection_open_ok
cli         |                                     [10,50] => connection_close
cli         |                                     [10,51] => connection_close_ok
cli         |                                     [10,60] => connection_blocked
cli         |                                     [10,61] => connection_unblocked
cli         |                                     [20,10] => channel_open
cli         |                                     [20,11] => channel_open_ok
cli         |                                     [20,20] => channel_flow
cli         |                                     [20,21] => channel_flow_ok
cli         |                                     [20,40] => channel_close
cli         |                                     [20,41] => channel_close_ok
cli         |                                     [30,10] => access_request
cli         |                                     [30,11] => access_request_ok
cli         |                                     [40,10] => exchange_declare
cli         |                                     [40,11] => exchange_declare_ok
cli         |                                     [40,20] => exchange_delete
cli         |                                     [40,21] => exchange_delete_ok
cli         |                                     [40,30] => exchange_bind
cli         |                                     [40,31] => exchange_bind_ok
cli         |                                     [40,40] => exchange_unbind
cli         |                                     [40,51] => exchange_unbind_ok
cli         |                                     [50,10] => queue_declare
cli         |                                     [50,11] => queue_declare_ok
cli         |                                     [50,20] => queue_bind
cli         |                                     [50,21] => queue_bind_ok
cli         |                                     [50,30] => queue_purge
cli         |                                     [50,31] => queue_purge_ok
cli         |                                     [50,40] => queue_delete
cli         |                                     [50,41] => queue_delete_ok
cli         |                                     [50,50] => queue_unbind
cli         |                                     [50,51] => queue_unbind_ok
cli         |                                     [60,10] => basic_qos
cli         |                                     [60,11] => basic_qos_ok
cli         |                                     [60,20] => basic_consume
cli         |                                     [60,21] => basic_consume_ok
cli         |                                     [60,30] => basic_cancel_from_server
cli         |                                     [60,31] => basic_cancel_ok
cli         |                                     [60,40] => basic_publish
cli         |                                     [60,50] => basic_return
cli         |                                     [60,60] => basic_deliver
cli         |                                     [60,70] => basic_get
cli         |                                     [60,71] => basic_get_ok
cli         |                                     [60,72] => basic_get_empty
cli         |                                     [60,80] => basic_ack_from_server
cli         |                                     [60,90] => basic_reject
cli         |                                     [60,100] => basic_recover_async
cli         |                                     [60,110] => basic_recover
cli         |                                     [60,111] => basic_recover_ok
cli         |                                     [60,120] => basic_nack_from_server
cli         |                                     [90,10] => tx_select
cli         |                                     [90,11] => tx_select_ok
cli         |                                     [90,20] => tx_commit
cli         |                                     [90,21] => tx_commit_ok
cli         |                                     [90,30] => tx_rollback
cli         |                                     [90,31] => tx_rollback_ok
cli         |                                     [85,10] => confirm_select
cli         |                                     [85,11] => confirm_select_ok
cli         |                                 )
cli         |
cli         |                         )
cli         |
cli         |                     [channel_id:protected] => 1
cli         |                     [msg_property_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                         (
    cli         |                             [str:protected] =>
cli         |                             [str_length:protected] => 0
cli         |                             [offset:protected] => 21
cli         |                             [bitcount:protected] => 0
cli         |                             [timeout:protected] => 0
cli         |                             [bits:protected] => 0
cli         |                             [io:protected] =>
cli         |                             [is64bits:protected] => 1
cli         |                         )
cli         |
cli         |                     [wait_content_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                         (
    cli         |                             [str:protected] =>
cli         |                             [str_length:protected] => 0
cli         |                             [offset:protected] => 12
cli         |                             [bitcount:protected] => 0
cli         |                             [timeout:protected] => 0
cli         |                             [bits:protected] => 0
cli         |                             [io:protected] =>
cli         |                             [is64bits:protected] => 1
cli         |                         )
cli         |
cli         |                     [dispatch_reader:protected] => PhpAmqpLib\Wire\AMQPReader Object
cli         |                         (
    cli         |                             [str:protected] =>
cli         |                             [str_length:protected] => 0
cli         |                             [offset:protected] => 47
cli         |                             [bitcount:protected] => 0
cli         |                             [timeout:protected] => 0
cli         |                             [bits:protected] => 0
cli         |                             [io:protected] =>
cli         |                             [is64bits:protected] => 1
cli         |                         )
cli         |
cli         |                 )
cli         |
cli         |             [consumer_tag] => amq.ctag-ile9LfbndGzfMfPscCmcRA
cli         |             [delivery_tag] => 1
cli         |             [redelivered] =>
cli         |             [exchange] =>
cli         |             [routing_key] => echo
    cli         |         )
cli         |
cli         |     [prop_types:protected] => Array
    cli         |         (
        cli         |             [content_type] => shortstr
cli         |             [content_encoding] => shortstr
cli         |             [application_headers] => table_object
cli         |             [delivery_mode] => octet
cli         |             [priority] => octet
cli         |             [correlation_id] => shortstr
cli         |             [reply_to] => shortstr
cli         |             [expiration] => shortstr
cli         |             [message_id] => shortstr
cli         |             [timestamp] => timestamp
cli         |             [type] => shortstr
cli         |             [user_id] => shortstr
cli         |             [app_id] => shortstr
cli         |             [cluster_id] => shortstr
cli         |         )
cli         |
cli         |     [properties:PhpAmqpLib\Wire\GenericContent:private] => Array
    cli         |         (
        cli         |             [delivery_mode] => 2
cli         |             [priority] => 1
cli         |             [expiration] => 46092000
cli         |             [timestamp] => 1606302707
cli         |         )
cli         |
cli         |     [serialized_properties:PhpAmqpLib\Wire\GenericContent:private] =>
cli         | )
    */
