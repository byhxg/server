<?php
namespace App\Component\Process;
class Process{
    private  $token;
    public  $pid;
    public  function  __construct($token='')
    {
        $this->token=$token;
        $this->run(); //创建子进程
        $this->signal();//信号监听，在子进程关闭时回收
    }

    //获取任务,创建进程
    protected  function  run(){
        $process=$this->create_process();
        $process->write($this->token); //主进程管道写入
    }

    /**
     * 创建子进程
     */
    protected  function  create_process(){
        $process=new \swoole\Process([$this,'callback_function'],true);
        $this->pid=$process->start(); //启动子进程
        return $process;
    }
    //子进程业务处理逻辑
    public  function callback_function($worker){
        //子进程接收
        $res=$worker->read();
        //执行转码推流 ffmpeg
        $worker->exec('/usr/bin/ffmpeg',['-i','rtmp://live.hkstv.hk.lxdns.com/live/hks','-metadata', 'title="(token='.$res.')"','-vcodec','libx264', '-f','flv', 'tcp://127.0.0.1:8099']);
        // rtmp://live.hkstv.hk.lxdns.com/live/hks  -r 25     -ar 44100 -acodec mp3   -profile baseline   -level:v 3.1  -tune zerolatency    -preset ultrafast  -metadata title="(token=xxxxxxxxx)"   -vcodec  libx264  -f flv  tcp://127.0.0.1:8099
    }

    //捕获子进程结束时的信号,回收子进程
    public  function  signal(){
        \swoole\process::signal(SIGCHLD, function($sig) {
            //必须为false，非阻塞模式
            while($ret = \swoole\process::wait(false)) {
            }
        });
    }

}
