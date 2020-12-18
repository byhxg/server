<?php
namespace ShineYork\Io\NonBlocking;
use ShineYork\Io\WorkerBase;

// 这是等会自个要写的服务
class Worker extends WorkerBase
{
    // 需要处理事情
    public function accept()
    {
        // 接收连接和处理使用
        while (true) {
            // 监听的过程是阻塞的
            $client = stream_socket_accept($this->socket);
            // is_callable判断一个参数是不是闭包
            if (is_callable($this->onConnect)) {
                // 执行函数
                call_user_func($this->onConnect,$this, $client);
            }
            // tcp 处理 大数据 重复多发几次
            // $buffer = "";
            // while (!feof($client)) {
            //    $buffer = $buffer.fread($client, 65535);
            // }
            $data = fread($client, 65535);
            if (is_callable($this->onReceive)) {
                call_user_func($this->onReceive,$this, $client, $data);
            }
//            fclose($client);
        }
    }

    public function send($client, $data)
    {
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/html;charset=UTF-8\r\n";
        $response .= "Connection: keep-alive\r\n";
        $response .= "Content-length: ".strlen($data)."\r\n\r\n";
        $response .= $data;
        try{
            var_dump($client,$data);
            fwrite($client, $response);
        }catch (\Exception $e){
            var_dump($e);exit;
        }
    }
}
