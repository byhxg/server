<?php
namespace SwoCloud\Server;

use Redis;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Server as SwooleWebSocketServer;
use SwooleStar\Console\Input;
use Swoole\Coroutine\Http\Client;

/**
 * 1. 检测IM-server的存活状态
 * 2. 支持权限认证
 * 3. 根据服务器的状态，按照一定的算法，计算出该客户端连接到哪台IM-server，返回给客户端，客户端再去连接到对应的服务端,保存客户端与IM-server的路由关系
 * 4. 如果 IM-server宕机，会自动从Redis中当中剔除
 * 5. IM-server上线后连接到Route，自动加 入Redis(im-server ip:port)
 * 6. 可以接受来自PHP代码、C++程序、Java程序的消息请求，转发给用户所在的IM-server
 * 7. 缓存服务器地址，多次查询redis
 *
 * 是一个websocket
 */
class Route extends Server
{
    protected $serverKey = 'im_server';

    protected $redis = null;

    protected $dispatcher = null;
    //获取服务器算法
    protected $arithmetic = 'round';

    public function onWorkerStart(SwooleServer $server, $worker_id)
    {
        //多个进程不能公用一个链接
        $this->redis = new Redis;
        $this->redis->pconnect("192.168.0.111", 6379);
    }

    public function onOpen(SwooleServer $server, $request)
    {
        dd('onOpen', __CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
    }

    public function onMessage(SwooleServer $server, $frame)
    {
//         dd('onMessage');
        // register
        // delete

        $data = \json_decode($frame->data, true);
        $fd   = $frame->fd;

        $this->getDispatcher()->{$data['method']}($this, $server, ...[$fd, $data]);
    }

    public function onClose(SwooleServer $ser, $fd)
    {
        dd("onClose");
    }

    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        if ($request->server['request_uri'] == '/favicon.ico') {
            $request->end('404');
            return null;
        }
        // 解决跨域
        $response->header('Access-Control-Allow-Origin', "*");
        $response->header('Access-Control-Allow-Methods', "GET,POST,OPTIONS");
        /*
        post 请求
        ['method' => 'login',nam]
         */
        $this->getDispatcher()->{$request->post['method']}($this, $request, $response);
    }


    protected function initEvent()
    {
        $this->setEvent('sub', [
            'request' => 'onRequest',
            'open'    => "onOpen",
            'message' => "onMessage",
            'close'   => "onClose",
        ]);
    }

    public function getDispatcher()
    {
        if(empty($this->dispatcher)) {
            $this->dispatcher = new Dispatcher;
        }
        return $this->dispatcher;
    }

    public function getRedis()
    {
        return $this->redis;
    }

    public function getArithmetic()
    {
        return $this->arithmetic;
    }

    public function getServerKey()
    {
        return $this->serverKey;
    }

    public function createServer()
    {
        $this->swooleServer = new SwooleWebSocketServer($this->host, $this->port);

        Input::info('WebSocket server 访问 : ws://0.0.0.0:'.$this->port);
    }

    /**
     * 指定给某一个连接的服务器发送信息
     * 2020年5月24日15:35:12
     * @param $ip
     * @param $port
     * @param $data
     * @param  [type] $ip   [description]
     * @return void [type]       [description]
     */
    public function send($ip, $port, $data, $header = null)
    {
        $cli = new Client($ip, $port);
        // 携带任务id
        $unipid = session_create_id();
        $data['msg_id'] = $unipid;

        empty($header)?:$cli->setHeaders($header);

        if ($cli->upgrade('/')) {
            $cli->push(\json_encode($data));
        }
        // 发送成功之后调用 是否确认接收
        $this->confirmGo($unipid, $data, $cli);
    }

    /**
     * 获取所有服务器的信息，可连接的
     * 2020年5月24日15:35:51
     * @return [type] [description]
     */
    public function getIMServers()
    {
        return $this->getRedis()->smembers($this->getServerKey());
    }

}
