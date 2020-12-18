<?php
// echo 1;
require __DIR__.'/../../../../vendor/autoload.php';
use ShineYork\Io\Reactor\Swoole\Mulit\Worker;
use ShineYork\Io\Index;
$host = "tcp://0.0.0.0:9000";
$server = new Worker($host);
// echo 1;
$server->onReceive = function($socket, $client, $data){
    (new Index)->index();
    send($client, "hello world client \n");
};
// debug($host);
$server->start();

// require __DIR__.'/../../../../vendor/autoload.php';
//
// $host = "0.0.0.0"; // 0.0.0.0 代表接听所有
// $serv = new Swoole\Server($host, 9000);
// $serv->on('Receive', function ($serv, $fd, $from_id, $data) {
//     (new Index)->index();
//     $serv->send($fd, "Server: ".$data);
// });
// $serv->start();
