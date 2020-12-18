<?php
namespace SwooleStar\Server;

use Redis;
use SwooleStar\RPC\Rpc;
use SwooleStar\Supper\Inotify;
use Swoole\Server as SwooleServer;
use SwooleStar\Foundation\Application;
use Swoole\Coroutine\Http\Client;

/**
 * 所有服务的父类， 写一写公共的操作
 */
abstract class Server
{
    // 属性
    /**
     * [protected description]
     * @var Swoole/Server
     */
    protected $swooleServer;

    protected $app ;

    protected $inotify = null;
//
//    public $port = 9502;
//
//    public $host = "0.0.0.0";

    protected $watchFile = true;

    /**
     * 用于记录系统pid的信息
     * @var string
     */
    protected $pidFile = "/runtime/swostar.pid";
    protected $config = [
        //异步进程默认为0 、不冲突
        'task_worker_num' => 0,
    ];
    /**
     * 用于记录pid的信息、用于重启服务使用
     * @var array
     */
    protected $pidMap = [
        'masterPid'  => 0,//
        'managerPid' => 0,
        'workerPids' => [],
        'taskPids'   => []
    ];
    /**
     * 注册的回调事件
     * [
     *   // 这是所有服务均会注册的时间
     *   "server" => [],
     *   // 子类的服务
     *   "sub" => [],
     *   // 额外扩展的回调函数
     *   "ext" => []
     * ]
     *
     * @var array
     */
    protected $event = [
        // 这是所有服务均会注册的时间
        "server" => [
            // 事件   =》 事件函数
            "start"        => "onStart",
            "managerStart" => "onManagerStart",
            "managerStop"  => "onManagerStop",
            "shutdown"     => "onShutdown",
            "workerStart"  => "onWorkerStart",
            "workerStop"   => "onWorkerStop",
            "workerError"  => "onWorkerError",
        ],
        // 子类的服务
        "sub" => [],
        // 额外扩展的回调函数
        // 如 ontart等
        "ext" => []
    ];

    public function __construct(Application $app )
    {
        $this->app = $app;
        // 初始化swoole配置
        $this->initSetting();
        // 1. 创建 swoole server
        $this->createServer();
        // 3. 设置需要注册的回调函数
        $this->initEvent();
        // 4. 设置swoole的回调函数
        $this->setSwooleEvent();
    }
    /**
     * 创建服务
     * 六星教育 @shineyork老师
     */
    protected abstract function createServer();
    /**
     * 初始化监听的事件
     */
    protected abstract function initEvent();

    // 通用的方法
    public function start()
    {
        $config = app('config');
        // 2. 设置配置信息
        $this->swooleServer->set($this->config);
        if ($config->get('server.rpc.tcpable')) {
            dd('启动服务并监听服务',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
            new Rpc($this->swooleServer, $config->get('server.rpc'));
        }
        //启动
        $this->swooleServer->start();
    }

    /**
     * 初始化设置
     */
    public function initSetting()
    {
        $config = app('config');
        $this->port = $config->get('server.http.port');
        $this->host = $config->get('server.http.host');
    }

    /**
     * 设置swoole的回调事件
     * 六星教育 @shineyork老师
     */
    protected function setSwooleEvent()
    {
        dd($this->event);
        foreach ($this->event as $type => $events) {
            foreach ($events as $event => $func) {
                $this->swooleServer->on($event, [$this, $func]);
            }
        }
    }

    /**
     * 热重启函数
     * @return callable
     */
    protected function watchEvent()
    {
        return function($event){
            $action = 'file:';
            switch ($event['mask']) {
                case IN_CREATE:
                    $action = 'IN_CREATE';
                    break;

                case IN_DELETE:
                    $action = 'IN_DELETE';
                    break;
                case \IN_MODIFY:
                    $action = 'IN_MODIF';
                    break;
                case \IN_MOVE:
                    $action = 'IN_MOVE';
                    break;
            }
            $this->swooleServer->reload();
        };
    }
    // 回调方法
    public function onStart(SwooleServer $server)
    {
        $this->pidMap['masterPid'] = $server->master_pid;
        $this->pidMap['managerPid'] = $server->manager_pid;

        if ($this->watchFile ) {
            $this->inotify = new Inotify( $this->watchEvent());
            $this->inotify->start();
        }

        //第一种事件绑定方式
        // dd($this->app->make('event')->getEvents());
        $this->app->make('event')->trigger('start', [$this]);
        //第二种 携程方式
//         go(function(){
//             $cli = new \Swoole\Coroutine\Http\Client('localhost', 9500);
//             $ret = $cli->upgrade("/");
//             $cli->push('1');
//             $cli->close();
//         });
    }
    public function onManagerStart(SwooleServer $server)
    {

    }
    public function onManagerStop(SwooleServer $server)
    {

    }
    public function onShutdown(SwooleServer $server)
    {

    }
    public function onWorkerStart(SwooleServer $server, int $worker_id)
    {
        $this->pidMap['workerPids'] = [
            'id'  => $worker_id,
            'pid' => $server->worker_id
        ];
        $this->redis = new Redis;
        $this->redis->pconnect("192.168.0.111", 6379);
    }
    public function onWorkerStop(SwooleServer $server, int $worker_id)
    {

    }
    public function onWorkerError(SwooleServer $server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
    }

    // GET | SET

    /**
     * @param array
     *
     * @return static
     */
    public function setEvent($type, $event)
    {
        // 暂时不支持直接设置系统的回调事件
        if ($type == "server") {
            return $this;
        }
        $this->event[$type] = $event;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return static
     */
    public function setConfig($config)
    {
        $this->config = array_map($this->config, $config);
        return $this;
    }

    public function watchFile($watchFile)
    {
        $this->watchFile = $watchFile;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }
    /**\
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    public function getRedis(){
        return $this->redis;
    }
    /**
     * 指定给某一个连接的服务器发送信息
     * 2020年5月24日20:10:22
     * @param      $ip
     * @param      $port
     * @param      $data
     * @param null $header
     * @return void [type]       [description]
     */
    public function send($ip, $port, $data, $header = null)
    {
        $cli = new Client($ip, $port);

        empty($header)?:$cli->setHeaders($header);

        if ($cli->upgrade('/')) {
            $cli->push(\json_encode($data));
        }
    }
}
