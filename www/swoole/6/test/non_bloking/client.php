<?php
foreach ([] as $key => $value) {
  // code...
}

// 是建立连接
$client = stream_socket_client("tcp://127.0.0.1:9501");
var_dump($client);echo (int) $client;
exit;
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

// swoole_timer_tick() // 异步获取
$read = $write = $except = null;
// stream_select
// 检测的方式根据数组 -》 去进行检测socket状态

while (!feof($client)) {
    // 接收的数据包的大小65535

    $read[] = $client;
    fread($client, 65535);
    // var_dump();
    // echo $r++."\n";
    sleep(1);
    echo "检查socket :\n";
    // 返回一个结果 0 可用 1，正忙
    var_dump(stream_select($read, $write, $except, 0));

    // foreach ($read as $value) {
    //   // code...
    // }
}
