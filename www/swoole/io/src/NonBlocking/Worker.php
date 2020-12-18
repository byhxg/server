<?php
namespace Php\Io\NonBlocking;

/**
 * 非阻塞
 */
class Worker
{
    // 自定义服务的事件注册函数，
    private $onReceive = null;
    //链接操作
    private $onConnect = null;
    //关闭操作
    private $onClose = null;
    // 连接
    public $socket = null;

    public function __construct($socket_address)
    {
        //创建套接字
        $this->socket = stream_socket_server($socket_address);
        echo $socket_address."\n";
    }

    // 需要处理事情
    public function accept()
    {
        // 接收连接和处理使用
        while(true) {
            $client = @stream_socket_accept($this->socket);
            // is_callable判断一个参数是不是闭包
            if(is_callable($this->onConnect)) {
                // 执行函数
                ($this->onConnect)($this, $client);
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
//            fclose($client);
        }
    }

    // 发送信息
    public function send($conn, $data)
    {
        var_dump($conn,$data);
        if($data==''||$data==false){
            return false;
        }
        echo 'send 数据：'.$data;
        $length = strlen($data);
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/html;charset=UTF-8\r\n";
        $response .= "Connection: keep-alive\r\n";
        $response .= "Content-length: ".$length."\r\n\r\n";
        $response .= $data;
        fwrite($conn, $response);
    }

    // 启动服务的
    public function start()
    {
        $this->accept();
    }

    /**
     * on方法绑定操作
     * @param $model
     * @param $param
     * @return bool
     */
    public function on($model, $param)
    {
        $model = 'on'.ucfirst($model);
        //检查属性是否存在
        $res = property_exists(__CLASS__, $model);
        if($res && is_callable($param)) {
            $this->$model = $param;
        }
        return true;
    }
}
