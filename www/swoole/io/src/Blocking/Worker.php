<?php
namespace Php\Io\Blocking;

use Php\Io\WorkerBase;

/**
 * 阻塞模型
 */
class Worker extends WorkerBase
{
    // 需要处理事情
    public function accept()
    {
        // 接收连接和处理使用
        while(true) {
            echo 'accept';
            $client = @stream_socket_accept($this->socket);
            // is_callable判断一个参数是不是闭包
            if(is_callable($this->onConnect)) {
                // 执行函数
                call_user_func($this->onConnect,$this, $client);
            }
            // tcp 处理 大数据 重复多发几次
            // $buffer = "";
            // while (!feof($client)) {
            //    $buffer = $buffer.fread($client, 65535);
            // }
            $data = fread($client, 65535);
            if(is_callable($this->onReceive)) {
                ($this->onReceive)($this, $client, $data);
            }
            // 处理完成之后关闭连接
            fclose($client);
        }
    }
}
