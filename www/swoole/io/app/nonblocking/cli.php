<?php
// 是建立连接
$client = stream_socket_client("tcp://127.0.0.1:9502");
stream_set_blocking($client,0);
$new = time();
// 给socket通写信息
// 粗暴的方式去实现
//while (true) {
    fwrite($client, "hello world");
    var_dump(fread($client, 65535));
//    sleep(2);
//}
// 读取信息

// 关闭连接
 fclose($client);
echo '其他业务'.PHP_EOL;


echo time()-$new.PHP_EOL;