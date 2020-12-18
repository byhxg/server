<?php

// 1. 创建swoole 默认创建的是一个同步的阻塞tcp服务
$host = "0.0.0.0"; // 0.0.0.0 代表接听所有
$post = 9500;
// 创建Server对象，监听 127.0.0.1:9501端口
// 默认是tcp
$serv = new Swoole\Server($host, $post);
// 添加配置
$serv->set([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024 * 12,
    // 这是类型
    'package_length_type'   => 'N',
    // 数据从 0开始
    'package_length_offset' => 0,
    // 4 是因为 我们选择的pack的类型是 N 是4 位
    'package_body_offset'   => 4,
]);
// 2. 注册事件
$serv->on('Start', function($serv) use ($host){
    echo "启动swoole 监听的信息tcp:$host:9500\n";
});

//监听连接进入事件
$serv->on('Connect', function ($serv, $fd) {
    echo "Client: Connect.\n";
});

//监听数据接收事件
$serv->on('Receive', function ($serv, $fd, $from_id, $data) {
    // 推荐这种方式解析 先截取长度，再去截取数据
    // $fooLen = unpack("n", substr($data, $count, 2))[1];

    // $pack = unpack('N', $data);
    // $content = $pack[1];
    // 对于粘包的数据进行 while 循环处理负责数据丢失

    // var_dump($content);
    // var_dump(substr($data, 4, $content));

    echo $data."\n";
//    $len = pack('N', strlen($context));
    // pack($data);
    // var_dump(explode("\r\n",$data));
    // echo "接受到".$fd."的信息\n";
    // echo "接受到".$data."\n";
    $serv->send($fd, "Server: ");
});

//监听连接关闭事件
$serv->on('Close', function ($serv, $fd) {
    echo "QQ离线.\n";
});

// 3. 启动服务器
// 阻塞
$serv->start(); // 阻塞与非阻塞
