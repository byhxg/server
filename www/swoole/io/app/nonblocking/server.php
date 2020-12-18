<?php
require __DIR__.'/../../vendor/autoload.php';
use Php\Io\NonBlocking\Worker;
$host = "tcp://0.0.0.0:9502";
$server = new Worker($host);

$server->on('connect', function($server, $client){
    echo PHP_EOL."有一个连接进来了".PHP_EOL;
//    $server->send($client, "hello world client 111".PHP_EOL);
//     fwrite($client, "server hellow");
});

$server->on('receive', function($socket, $client, $data){
    echo '数据：'.$data.PHP_EOL;
//    sleep(5);
    $socket->send($client, $data.PHP_EOL);
});

$server->start();
