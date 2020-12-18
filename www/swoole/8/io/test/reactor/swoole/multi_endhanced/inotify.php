<?php
// 初始化inotify
$fd = inotify_init();
var_dump($fd);
//  inotify_add_watch 针对于某一个文件进行监听
//  string $pathname , 监听文件的地址
//  int $mask ， 监听事件  IN_MODIFY

// foreach ($variable as $key => $value) {
    // 不能针对 -》 一个一个文件的监听
    $watch_descriptor = inotify_add_watch($fd, __DIR__.'/index.php', IN_MODIFY);
// }

// 读取发送变化的文件
// 是一个阻塞的
// while (true) {
//
// }

echo "1";
swoole_event_add($fd, function($fd){
  $events = inotify_read($fd);
  var_dump($events);
});
