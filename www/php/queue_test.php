<?php

/**
 * php queue_test.php
 * php queue_test.php consumer
 */

//生成一个消息队列的key
$msg_key = ftok(__FILE__, 'a');
$msg_key = 6969;
//产生一个消息队列
$msg_queue = msg_get_queue($msg_key, 0666);

$fun = isset($argv[1])?$argv[1]:'producer';
if(function_exists($fun)){
    $fun();
}

function producer(){
    global $msg_key, $msg_queue;
//检测一个队列是否存在 ,返回boolean值
    $status = msg_queue_exists($msg_key);
//可以查看当前队列的一些详细信息
    $message_queue_status =  msg_stat_queue($msg_queue);

//将一条消息加入消息队列
    while(true){
        $message = date('Y-m-d H:i:s').' '.uniqid();
        echo $message.PHP_EOL;
        msg_send($msg_queue, 1, $message);
        usleep(1000000);
    }
}

function consumer(){
    global $msg_queue;
    while(true){
        //从消息队列中读取一条消息。
        if (!msg_receive($msg_queue, 1, $type, 16384, $message, false, MSG_IPC_NOWAIT | MSG_NOERROR)) {
            sleep(1);
            continue;
        }
        echo $message;
    }
}
//移除消息队列
//msg_remove_queue($msg_queue);


/**
 * msg_send 有三个必选参数
 * resource $queue ,
 * int $msgtype ,
 * mixed $message
 *
 * 第一个必须要是队列资源类型。resource(4) of type (sysvmsg queue)
 * 第二个参数是消息类型，一个整形，且必须大于0.
 * msg_send() sends a message of type msgtype (which MUST be greater than 0) to the message queue specified by queue.
 * 第三个参数。是要发送的信息。可以是字符串，也可以是数组。默认会被serialize.
 */


/**
 * msg_receive 的参数比较多。必须要填的参数有5个。
 * resource $queue ,
 * int $desiredmsgtype ,
 * int &$msgtype ,
 * int $maxsize ,
 * mixed &$message
 *
 * 其中$desiredmsgtype .经过测试和官网描述不符，暂不解释。
 *
 * $msgtype 。这个是msg_send 中所选定的msg_type.这是一个引用参数。
 * The type of the message that was received will be stored in this parameter.
 *
 * $maxsize。
 * The maximum size of message to be accepted is specified by the maxsize;
 * if the message in the queue is larger than this size the function will fail (unless you set flags as described below).
 * 这个参数声明的是一个最大的消息大小，如果超过则会报错。
 *
 * $message.
 * 上文msg_send 发送的消息类型。
 */