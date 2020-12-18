<?php
ini_set('memory_limit','1024M');
// 是建立连接
$client = stream_socket_client("tcp://127.0.0.1:9011");
// var_dump($client);
// 给socket通写信息
// 粗暴的方式去实现
while (true) {
    $res = false;
    var_dump('>>>>',$client);
    $res = fwrite($client, time());
//    if ($res) {
//        $time = time();
//    }
//    if ($time<time()) {
//        echo 'Client '  . ' connect again' . PHP_EOL;
//        fclose($client);
//        $client = stream_socket_client("tcp://127.0.0.1:9011");
//    }

//    echo "===》 信息发送成功 \n";
    //var_dump(fread($client, 234234343));
    sleep(2);
}

