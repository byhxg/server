<?php
namespace Php\Io\Multi;

/**
 * 多路复用（本质是非阻塞-串行处理）
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
    //保存连接信息
    protected $sockets = [];
    //保存连接详情
    protected $socketInfo = [];

    /**
     * @param      $socket_address 地址:ip
     * @param bool $is_blocking 是否支持非阻塞
     */
    public function __construct($socket_address, $is_blocking = false)
    {
        $this->socket = stream_socket_server($socket_address);
        if($is_blocking){
            stream_set_blocking($this->socket, 0);
        }
        // 咋们的server也有忙的时候
        $this->sockets[(int) $this->socket] = $this->socket;
    }

    // 需要处理事情
    public function accept()
    {
        // 接收连接和处理使用
        while (true) {
            $read = $this->sockets;
            // 校验池子是否有可用的连接 -》 校验传递的数组中是否有可以用的连接 socket
            // 把连接放到$read
            // 它返回值其实并不是特别可靠

            //检测是否有可读资源
//             $this->debug('这是stream_select 检测之 start 的 $read');
//             $this->debug($read, true);
            stream_select($read, $w, $e, 1);

//             $this->debug('这是stream_select 检测之 end 的 $read');
//             $this->debug($read, true);
            // sleep(1);
            foreach ($read as $socket) {
                // $socket 可能为
                if ($socket === $this->socket) {
                    // 创建与客户端的连接
                    $this->createSocket();
                } else {
                    // 发送信息
                    $this->sendMessage($socket);
                }
                // 1. 主worker
                // 2. 也可能是通过 stream_socket_accept 创建的连接
            }
            // // 监听的过程是阻塞的
            // $client = stream_socket_accept($this->socket);
            // // is_callable判断一个参数是不是闭包
            // if (is_callable($this->onConnect)) {
            //     // 执行函数
            //     ($this->onConnect)($this, $client);
            // }
            // // tcp 处理 大数据 重复多发几次
            // // $buffer = "";
            // // while (!feof($client)) {
            // //    $buffer = $buffer.fread($client, 65535);
            // // }
            // $data = fread($client, 65535);
            // if (is_callable($this->onReceive)) {
            //     ($this->onReceive)($this, $client, $data);
            // }
            // 处理完成之后关闭连接
            //
            //
            // 心跳检测 - 自己的心跳
            // fclose($client);
        }
    }

    public function createSocket()
    {
        $client = stream_socket_accept($this->socket);
        // is_callable判断一个参数是不是闭包
        if (is_callable($this->onConnect)) {
            // 执行函数
            // ($this->onConnect)($this, $client);
        }
        // 把创建的socket的连接 -》 放到 $this->sockets
        $this->sockets[(int) $client] = $client;
    }

    public function sendMessage($client)
    {
        $data = fread($client, 65535);
        if ($data === '' || $data == false) {
            // 关闭连接
            // fclose($client);
            // unset($this->sockets[(int) $client]);
            return null;
        }
        if (is_callable($this->onReceive)) {
            ($this->onReceive)($this, $client, $data);
        }
    }

    // 发送信息
    public function send($conn, $data)
    {
        if($data==''||$data==false){
            return false;
        }
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

    /**
     * @param $data
     */
    public function debug($data)
    {
        if (!is_string($data)) {
            var_dump($data).PHP_EOL;
        } else {
            echo "==== >>>> : ".$data.PHP_EOL;
        }
    }
}
     