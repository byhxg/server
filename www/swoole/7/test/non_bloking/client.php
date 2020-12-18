<?php
// 是建立连接
$client = stream_socket_client("tcp://127.0.0.1:9501");
var_dump($client);echo (int) $client;
// 设置为非阻塞的状态
// stream_set_blocking
stream_set_blocking($client, 0);
$new = time();
// 给socket通写信息
// 粗暴的方式去实现
fwrite($client, "hello world");// 创建订单

echo "其他的业务\n"; // 响应 --
echo  time()-$new."\n";

$r = 0;

// 模拟定时器
while (!feof($client)) {
    // 接收的数据包的大小65535
    $read[] = $client;
    var_dump(fread($client, 65535));
    echo $r++."\n";
    sleep(1);
}

// stream_select
// 检测的方式根据数组 -》 去进行检测socket状态

$read = $write = $except = null;
echo "检查socket :\n";
var_dump(stream_select($read, $write, $except, 0));
