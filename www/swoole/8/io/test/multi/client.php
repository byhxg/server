<?php
// 是建立连接
$client = stream_socket_client("tcp://127.0.0.1:9000");
$new = time();
fwrite($client, "hello world");
var_dump(fread($client, 65535));
echo time() - $new ."\n";
// 给socket通写信息
// 粗暴的方式去实现
// while (true) {
//   echo "===》 准备发送信息 \n";
//   fwrite($client, "helslo world");
//
//   echo "===》 信息发送成功 \n";
//   var_dump(fread($client, 65535));
//   // sleep(2);
// }
