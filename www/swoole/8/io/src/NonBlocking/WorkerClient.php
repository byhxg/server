<?php
namespace ShineYork\Io\NonBlocking;

class WorkerClient{
    protected $socket;
    protected $error;
    protected $errstr;
    // 回调事件
    // function (WorkerClient $client, $data){}
    public $recv;
    public function __construct($socketAddress)
    {
        $this->socket = stream_socket_client($socketAddress, $this->errno, $this->errstr);
        // 转换到非阻塞模式
        stream_set_blocking($this->socket, 0);
    }

    public function send($data)
    {
        // 发送信息
        fwrite($this->socket, $data);
    }
    // 同步的 “定时器”
    public function recv()
    {
        $r = 0;
        if(is_callable($this->recv)){
            //触发时间的消息接收事件
            //传递到接收消息事件》当前连接、接收到的消息
            // call_usero _func($this->onMessage,$this,$client,$buffer);
            while (!feof($this->socket)) {
                sleep(1);
                echo $r++."\n";
                // 执行事情
                $buffer = fread($this->socket, 65535);
                if (!empty($buffer)) {
                    call_user_func($this->recv, $this, $buffer);
                }
            }
        }
        fclose($this->socket);
    }
    // 使用swoole定时器
    public function swooleRecv($client)
    {
        $r = 0;
        if(is_callable($this->recv)){
          swoole_timer_tick(1000, function ($timer_id) use ($client, &$r) {
              echo $r++."\n";
              if (!\feof($client->socket)) {
                  $buffer = fread($client->socket, 65535);
                  if (!empty($buffer)) {
                      call_user_func($client->recv, $client, $buffer);
                  }
              } else {
                  echo "close";
                  fclose($client->socket);
                  swoole_timer_clear($timer_id);
              }
          });
        }
    }
    public function forkRecv()
    {
        // 这是子进程定时器
        // 自己想
    }
}
