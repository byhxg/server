<?php
require __DIR__.'/../../vendor/autoload.php';
use Php\Io\Blocking\Worker;
$host = "tcp://0.0.0.0:9502";
$server = new Worker($host);

$server->on('connect', function($server, $client){
    echo "有一个连接进来了".PHP_EOL;
    send($client, "hello world client 111".PHP_EOL);
     fwrite($client, "server hellow");
});

$server->on('receive', function($socket, $client, $data){
    echo "给连接发送信息".PHP_EOL;
    $socket->send($client, $data.PHP_EOL);
});

$server->start();
