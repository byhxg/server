<?php

namespace Php\io;

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