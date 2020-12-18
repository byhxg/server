<?php
namespace ShineYork\Io;

/**
 * 基类
 */
class WorkerBase
{
    /**
     * @var null
     */
    public $onReceive = null;

    /**
     * 连接
     * @var null
     */
    public $onConnect = null;

    /**
     * 关闭
     * @var null
     */
    public $onClose = null;

    // 连接
    public $socket = null;

    //初始化服务
    public function __construct($socket_address)
    {
        $this->socket = stream_socket_server($socket_address);
        echo $socket_address."\n";
    }

    /**
     * 启动服务的
     */
    public final function start()
    {
        $this->accept();
    }
}
