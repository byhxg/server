<?php
namespace ShineYork\Io\Reactor\Swoole\MulitEndhanced\Traits;

use Swoole\Event;

trait ServerTraits
{
    // 自定义服务的事件注册函数，
    // 这三个是闭包函数
    public $onReceive = null;
    public $onConnect = null;
    public $onClose = null;
    // 连接
    protected $socket = null;
    /**
     * 记录客户端的信息 比如上一次连接的时间
     * @var [type]
     */
    protected $clients = [];
    /**
     * 记录产生的定时器
     * @var [type]
     */
    protected $timeIds = [];
    /**
     * 处理客户端的连接和信息发送
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function accept()
    {
        Event::add($this->initServer(), $this->createSocket());
        debug(posix_getpid()."进程 设置 event 成功");
    }
    /**
     * 初始化server socket也就是服务端
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function initServer()
    {
        // 并不会起到太大的影响
        // 这里是参考与workerman中的写法
        $context = stream_context_create($this->config['context']);
        // 设置端口可以重复监听
        \stream_context_set_option($context, 'socket', 'so_reuseport', 1);

        // 传递一个资源的文本 context
        return $this->socket = stream_socket_server($this->socket_address , $errno , $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
    }
    /**
     * 创建一个新的客户端连接信息
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function createSocket()
    {
        return function($socket){
            // $client 是不是资源 socket
            $client = stream_socket_accept($this->socket);
            // is_callable判断一个参数是不是闭包
            if (is_callable($this->onConnect)) {
                // 执行函数
                ($this->onConnect)($this, $client);
            }
            // 默认就是循环操作
            Event::add($client, $this->sendClient());
        };
    }
    /**
     * 给连接的客户端发送信息
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function sendClient()
    {
        return function($socket){
            // 如果能接收到信息，那么这个程序一定在心跳检测的范围内
            if (!empty($this->timeIds[(int) $socket])) {
                swoole_timer_clear($this->timeIds[(int) $socket]);
                debug("清空: ". $this->timeIds[(int) $socket]. "定时器");
            }
            //从连接当中读取客户端的内容
            $buffer=fread($socket,1024);
            //如果数据为空，或者为false,不是资源类型
            if(empty($buffer)){
                if(feof($socket) || !is_resource($socket)){
                    //触发关闭事件
                    swoole_event_del($socket);
                    fclose($socket);
                    debug('断开连接: '.(int) $socket);
                    // 避免程序继续往后执行
                    return null;
                }
            }
            //正常读取到数据,触发消息接收事件,响应内容
            if(!empty($buffer) && is_callable($this->onReceive)){
                ($this->onReceive)($this, $socket, $buffer);
            }
            // 定时器
            $this->heartbeatCheck($socket);
        };
    }
    /**
     * 创建出工作进程，并让每一个子进程去监听和处理客户端连接
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function fork()
    {
        for ($i=0; $i < $this->config['worker_num']; $i++) {
            $son11 = pcntl_fork();
            if ($son11 > 0) {
                // 父进程空间
                pidPut($son11, $this->config['worker_pid_files']);
            } else if($son11 < 0){
                // 进程创建失败的时候
            } else {
                $this->accept();
                // 处理接收请求
                exit;// 终止程序的执行
            }
        }
    }
    /**
     * 用于心跳检测 默认不开启
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function heartbeatCheck($socket)
    {
        $time = $this->config['heartbeat_check_interval'];
        if (!empty($time)) {
            // 记录客户端上一次信息的发送时间
            $this->clients[(int) $socket] = time();
            // 设置在多久后检测是否还有在连接
            $timeId = swoole_timer_after($time * 1000, function() use ($time, $socket){
                // 判断客户端是否，在heartbeat_check_interval 这个时间内是否还有信息的动作
                // 实际上当这个函数执行的时候已经端口连接了 -》超出了心跳检测的时间；原则上说下面的判断实际是无意义的
                if ((time() - ($this->clients[(int) $socket])) >= $time) {
                    swoole_event_del($socket);
                    \fclose($socket);
                    unset( $this->clients[(int) $socket]);
                    debug("结束：". (int) $socket. " 连接");
                }

                debug("执行 swoole_timer_after");
            });
            // 记录定时器
            $this->timeIds[(int) $socket] = $timeId;
        }
    }
}
