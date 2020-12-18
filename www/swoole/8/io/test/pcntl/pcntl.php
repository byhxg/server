<?php
$son = pcntl_fork();

// if ($son > 0) {
//   // 父进程空间
//   $son11 = pcntl_fork();
// } else if($son < 0){
//   // 。。。
// } else {
//   // 小于0子进程空间
//   echo $son." : i\n";
//   echo $son11." : i\n";
// }

for ($i=0; $i < 2; $i++) {
    $son11 = pcntl_fork();
    if ($son11 > 0) {
        // 父进程空间
        echo posix_getpid()."\n";
    } else if($son11 < 0){
        // 进程创建失败的时候
    } else {
        echo posix_getpid()."\n";
        // 小于0子进程空间
        echo $son11." : i\n";
        // break;
        exit;
    }
}

// 配合 for循环
if ($son11 > 0) {
    $status = 0;
    pcntl_wait($status);
    var_dump(); // 阻塞
}

// 进程回收



while (true) {
  // code...
}
// 1. 子进程的pid
// 2. 0
// 3. 负值-》代表失败
