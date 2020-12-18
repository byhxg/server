<?php

$config = array();
$config['host'] = '192.168.5.51';
$config['kport'] = 9092;
$config['main_topic'] = "69xiu";
$config['timeout'] = 3000;
//系统队列key
$config['msg_queue_key'] = 6969;

//分区id
$partition = 0;

if (!extension_loaded('sysvmsg')) {
    die('sysvmsg module is required');
}
$queue = msg_get_queue($config['msg_queue_key'], 0666);
if (!is_resource($queue)) {
    die('failed open msg queue');
}

$fun = isset($argv[1])?$argv[1]:'consumer';
if(function_exists($fun)){
    $fun();
}

function consumer(){
    global $partition,$config;
    $conf = new RdKafka\Conf();
    $conf->set('group.id', 'consumer'.$partition);

    $rk = new RdKafka\Consumer($conf);
    $broker = $config["host"] . ":" . $config["kport"];
    $rk->addBrokers($broker);

    $topicConf = new RdKafka\TopicConf();
    $topicConf->set('auto.commit.interval.ms', 300);
    $topic = $rk->newTopic($config['main_topic'], $topicConf);
    /**
     * consumeStart
     *   第一个参数标识分区，生产者是往分区0发送的消息，这里也从分区0拉取消息
     *   第二个参数标识从什么位置开始拉取消息，可选值为
     *     RD_KAFKA_OFFSET_BEGINNING : 从开始拉取消息
     *     RD_KAFKA_OFFSET_END : 从当前位置开始拉取消息
     *     RD_KAFKA_OFFSET_STORED : 从未确定消费数据开始
     */
    $topic->consumeStart($partition, RD_KAFKA_OFFSET_STORED);

    while(true) {
        try{
            $message = $topic->consume($partition, 120*10000);
            handle($message);
        } catch(Exception $e) {
            echo "[code:" . $e->getCode() . "]" . $e->getMessage().PHP_EOL;
        }
    }
}

function handle($message){
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            if ($message->payload != "") {
                echo "[offset:" . $message->offset . "]==" . $message->payload.PHP_EOL;
            }
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            //Broker: No more messages
            //echo "No more messages; will wait for more\n";
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            logMessage("Timed out", "consumerRoomException");
            break;
        default:
            throw new \Exception($message->errstr(), $message->err);
            break;
    }
}

function excePid($message){
    $pid = pcntl_fork();
    if ($pid == - 1) {
        // 错误处理：创建子进程失败时返回-1.
        die('could not fork');
    }else if($pid){
        pcntl_waitpid($pid, $status);
    }else{
        echo $message;
        exit;
    }
    return true;
}