<?php
use \Event as Event;
use \EventBase as EventBase;

$eventBase = new EventBase();
$event = new Event($eventBase, -1, Event::PERSIST | Event::TIMEOUT, function(){
    echo "hello world event \n";
});
$event1 = new Event($eventBase, -1, Event::PERSIST |  Event::TIMEOUT, function(){
    sleep(2);
    echo "hello world event -0.2 \n";
});
$event1->add(0.3);
$event->add(0.1);
$eventBase->loop();// 调用设置在eventBase中的事件 -》 swoole-》start启动事件

// 这个类的问题

// EventBase =>
// event是一个事件
// event -》 add 是不是添加一个事件
// Event Base =》 事件 库 =》 存储创建的事件
// $eventBase->loop(); 执行事件
// Event::PERSIST 表示事件循环执行
// Event::TIMEOUT 表示间隔多久执行
//
// | 累加事件
//
//  Event::__construct ( EventBase $base , mixed $fd , int $what , callable $cb [, mixed $arg = NULL ] )
//  $fd
//     -1 : 计时器
//     信号 ： 信号的标识  SIGIO， SIGHUP
//     socket ： 传递socket资源

// 问题
// 1. persist timeout 是针对回调的吗？ -》 是针对闭包函数 不针对event对象类
// 2. 如果没有timeout直接add就可以了么？-》 就是不要0.1 0.2
// 3. 不添加 add 那行代码 会是啥样  -》 add是可以不用传递 ， 传递参数只是代表时间
// 4. 第一个加persist 第二个不加 什么效果？
// 5. timeout 参数是先间隔在执行还是 先执行在间隔啊？ 0 - 1
