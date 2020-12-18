<?php
$client = new swoole_client(SWOOLE_SOCK_TCP);

// pack
// 	WARNING	zim_swoole_client_recv (ERRNO 1201): Package is too big. package_length=1399157370
// 	1. 服务端也需要pack一下
// 	2. 数据的确过大
//
// $client->set([
//     'open_length_check'     => true,
//     'package_max_length'    => 1024 * 1024 * 1,
//     // 这是
//     'package_length_type'   => 'N',
//     // 数据从 0开始
//     'package_length_offset' => 0,
//     // 4 是因为 我们选择的pack的类型是 N 是4 位
//     'package_body_offset'   => 4,
// ]);
//连接到服务器
if (!$client->connect('127.0.0.1', 9500, 0.5))
{
    die("connect failed.");
}
// 准备要发送的数据
$context = "1";
$len = pack('N', strlen($context));
// 向服务器发送数据
// 连续而又快速的向服务端发送很多个信息
for ($i=0; $i < 10; $i++) {
    $send = $len.$context;
    $client->send($context);
}
//
//

// $client->send(str_repeat('0oo', 1024 * 1024 * 1));
//从服务器接收数据
$data = $client->recv();
if (!$data)
{
    die("recv failed.");
}

// [2019-12-11 03:42:32 *35211.2]	NOTICE	swFactoryProcess_finish (ERRNO 1004): send 21854 byte failed, because connection[fd=2] is closed

// echo $data;
//关闭连接
// $client->close();

echo "<br>同步客户端<br>\n";
