<?php
/**
 * 这段代码模拟了一个日常的任务。
 * 第一个父进程产生了一个子进程。子进程又作为父进程，产生10个子进程。
 * 可以简化为A -> B -> c,d,e... 等进程。
 * 作为A来说，只需要生产任务，然后交给B 来处理。B 则会将任务分配给10个子进程来进行处理。
 *
 */

//设定脚本永不超时
set_time_limit(0);
$ftok = ftok(__FILE__, 'a');
$msg_queue = msg_get_queue($ftok);
$pidarr = [];

//产生子进程
$pid = pcntl_fork();
if ($pid) {
    //父进程模拟生成一个特大的数组。
    $arr = range(1,100000);

    //将任务放进队里，让多个子进程并行处理
    foreach ($arr as $val) {
        $status = msg_send($msg_queue,1, $val);
        usleep(1000);
    }
    $pidarr[] = $pid;
    msg_remove_queue($msg_queue);
} else {
    //子进程收到任务后，fork10个子进程来处理任务。
    for ($i =0; $i<10; $i++) {
        $childpid = pcntl_fork();
        if ($childpid) {
            $pidarr[] = $childpid; //收集子进程processid
        } else {
            while (true) {
                msg_receive($msg_queue, 0, $msg_type, 1024, $message);
                if (!$message) exit(0);
                echo $message.PHP_EOL;
                usleep(1000);
            }
        }
    }
}

//防止主进程先于子进程退出，形成僵尸进程
while (count($pidarr) > 0) {
    foreach ($pidarr as $key => $pid) {
        $status = pcntl_waitpid($pid, $status);
        if ($status == -1 || $status > 0) {
            unset($pidarr[$key]);
        }
    }
    sleep(1);
}
/*
以上的示例只是为了说明多进程通信的应用示例，并未在真实的项目中应用。为了示例方便，省略了很多的校验条件。但作为了解过程及原理来说，并不影响。
在执行while 循环时候，必须要使用usleep(1000) 以上。否则CPU可能会被撑爆。
以上的多进程通信，没有产生僵尸进程。得益于最后一段的while循环。
其原理在于，父进程在每次循环的时候都检测子进程是否退出。如果退出，则父进程就会回收该子进程。并且将该进程从进程列表中删除。
可以使用ps aux |grep process.php来查看当前产生的进程数量。 其中process.php 是运行的文件名
效果如下：