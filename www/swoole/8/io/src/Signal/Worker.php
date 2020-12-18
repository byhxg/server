<?php
namespace ShineYork\Io\Signal;

// 这是等会自个要写的服务
class Worker
{

    // 自定义服务的事件注册函数，
    // 这三个是闭包函数
    public $onReceive = null;
    public $onConnect = null;
    public $onClose = null;

    // 连接
    public $socket = null;

    public function __construct($socket_address)
    {
        $this->socket = stream_socket_server($socket_address);
        echo $socket_address."\n";
    }

    // 需要处理事情
    public function accept()
    {
        // 接收连接和处理使用
        while (true) {
            $this->debug("accept start");

            // 监听的过程是阻塞的
            $client = stream_socket_accept($this->socket);
            pcntl_signal(SIGIO, $this->sigHander($client));
            posix_kill(posix_getpid(), SIGIO);
            // 分发
            pcntl_signal_dispatch();

            $this->debug("accept end");
            // 处理完成之后关闭连接
            // 心跳检测 - 自己的心跳
            // fclose($client);
        }
    }
    public function sigHander($client)
    {
        return function($sig) use ($client){
            // is_callable判断一个参数是不是闭包
            if (is_callable($this->onConnect)) {
                // 执行函数
                ($this->onConnect)($this, $client);
            }
            $data = fread($client, 65535);
            if (is_callable($this->onReceive)) {
                ($this->onReceive)($this, $client, $data);
            }
        };
    }
    public function debug($data, $flag = false)
    {
        if ($flag) {
            var_dump($data);
        } else {
            echo "==== >>>> : ".$data." \n";
        }
    }
    // 发送信息
    public function send($client, $data)
    {
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/html;charset=UTF-8\r\n";
        $response .= "Connection: keep-alive\r\n";
        $response .= "Content-length: ".strlen($data)."\r\n\r\n";
        $response .= $data;
        fwrite($client, $response);
    }


    // 启动服务的
    public function start()
    {
        $this->accept();
    }
}
