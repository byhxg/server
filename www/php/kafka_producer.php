<?php

$config = array();
$config['host'] = '192.168.5.51';
$config['kport'] = 9092;
$config['main_topic'] = "69xiu";
$config['timeout'] = 3000;
//系统队列key
$config['msg_queue_key'] = 6969;

//Topic：特指Kafka处理的消息源（feeds of messages）的不同分类。
//Partition：Topic物理上的分组，一个topic可以分为多个partition，每个partition是一个有序的队列。partition中的每条消息都会被分配一个有序的id（offset）。
//Message：消息，是通信的基本单位，每个producer可以向一个topic（主题）发布一些消息。
//Producers：消息和数据生产者，向Kafka的一个topic发布消息的过程叫做producers。
//Consumers：消息和数据消费者，订阅topics并处理其发布的消息的过程叫做consumers。
//Broker：缓存代理，Kafa集群中的一台或多台服务器统称为broker。

if (!extension_loaded('sysvmsg')) {
    die('sysvmsg module is required');
}
$queue = msg_get_queue($config['msg_queue_key'], 0666);
if (!is_resource($queue)) {
    die('failed open msg queue');
}

$fun = isset($argv[1])?$argv[1]:'producer';
if(function_exists($fun)){
    $fun();
}

function producer(){
    global $config,$queue;
    $rk = new RdKafka\Producer();
    $rk->setLogLevel(LOG_DEBUG);

    $broker = $config["host"] . ":" . $config["kport"];
    $rk->addBrokers($broker);
    $topic = $rk->newTopic($config['main_topic']);

    $partition = 0;
    while(1){
        //16384 大小;
        if (!msg_receive($queue, 1, $type, 16384, $message, false, MSG_IPC_NOWAIT | MSG_NOERROR)) {
            sleep(1);
            continue;
        }
        echo "{$partition} | {$message}".PHP_EOL;
        $topic->produce($partition, 0, $message);
    }
}
