<?php
// 是建立连接
$client = stream_socket_client("tcp://127.0.0.1:9502");
$new = time();
// 给socket通写信息
// 粗暴的方式去实现
while (true) {
    fwrite($client, "hello world - ".mt_rand(0,99));
    var_dump(fread($client, 65535));
    sleep(2);
}
// 读取信息