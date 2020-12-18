<?php
namespace ShineYork\Io\Reactor\Swoole\MulitEndhanced;

use ShineYork\Io\Util\Inotify;
// 这是等会自个要写的服务
class Worker
{
    // 这是处理文件监听 - 》 热加载
    use Traits\InotifyTraits;
    // 这是worker server的超类
    use Traits\ServerTraits;
    // 这是设置信号的超类
    use Traits\SignalTraits;
    // 创建多个子进程 -》 是不是可以自定义
    protected $config = [
        'worker_num' => 4,
        'worker_pid_files' => __DIR__."/pid/workerPids.txt",
        'master_pid_files' => __DIR__."/pid/masterPids.txt",
        'context' => [
            'socket' => [
                // 设置等待资源的个数
                'backlog' => '102400',
            ],
        ],
        'watch_file' => false,
        // 以秒作为单位
        'heartbeat_check_interval' => 3
    ];
    protected $socket_address = null;
    protected $inotify = null;

    public function __construct($socket_address)
    {
        $this->socket_address = $socket_address;
    }

    public function reload()
    {
        $this->stop(false);
        pidPut(null, $this->config['worker_pid_files']);
        $this->fork();
    }

    public function stop($masterKill = true)
    {
        // 杀死子进程
        $workerPids = pidGet($this->config['worker_pid_files']);
        foreach ($workerPids  as $key => $workerPid) {
            posix_kill($workerPid, 9);
        }
        // 杀死父进程
        if ($masterKill) {
            $masterPid = (pidGet($this->config['master_pid_files']))[0];
            posix_kill($masterPid, 9);
            $this->inotify->stop();
        }
    }

    // 启动服务的
    public function start()
    {
        debug('start 开始 访问：'.$this->socket_address);
        pidPut(null, $this->config['worker_pid_files']);
        pidPut(null, $this->config['master_pid_files']);

        // 记录的是父进程pid
        pidPut(posix_getpid(), $this->config['master_pid_files']);

        debug("当前主进程的pid：".posix_getpid());
        // 是否对文件进行监控重启
        if ($this->config['watch_file']) {
            $this->inotify = new Inotify(basePath(), $this->watchEvent());
            $this->inotify->start();
        }

        $this->fork();
        $this->monitorWorkersForLinux();
    }

    public function set($data)
    {
        foreach ($data as $key => $value) {
            $this->config[$key] = $value;
        }
    }
}
