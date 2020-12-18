<?php
namespace SwooleStar\Server\WebSocket;

use SwooleStar\Console\Input;

use SwooleStar\Server\Http\HttpServer;
use Swoole\WebSocket\Server as SwooleServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

class WebSocketServer extends HttpServer
{
    public function createServer()
    {
        $this->swooleServer = new SwooleServer('0.0.0.0', $this->port);
        dd('WebSocket server 访问 : ws://'.$this->host.':'.$this->port );
    }

    public function initSetting()
    {
        $config = app('config');
        $this->port = $config->get('server.ws.port');
        $this->host = $config->get('server.ws.host');
        $this->config = $config->get('server.ws.swoole');
    }

    protected function initEvent(){
        $event = [
            'request' => 'onRequest',
            'open' => "onOpen",
            'message' => "onMessage",
            'close' => "onClose",
        ];
        // 判断是否自定义握手的过程
        ( ! $this->app->make('config')->get('server.ws.is_handshake'))?: $event['handshake'] = 'onHandShake';
        $this->setEvent('sub', $event);
    }

    public function onHandShake(Request $request, Response $response){
        $this->app->make('event')->trigger('ws.hand', [$this, $request, $response]);
        // 因为设置了onHandShake回调函数，就不会触发onOpen
        $this->onOpen($this->swooleServer, $request);
    }

    public function onOpen(SwooleServer $server, $request) {
        dd($request->server,__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        // 需要获取访问的地址
        Connections::init($request->fd, $request);
        app('route')->setFlag('WebSocket')->setMethod('open')->match($request->server['path_info'], [$server, $request]);
    }

    public function onMessage(SwooleServer $server, $frame) {
        $path = (Connections::get($frame->fd))['path'];
        dd($path,__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
         // 消息回复事件
        $this->app->make('event')->trigger('ws.message.front', [$this, $server, $frame]);
        app('route')->setFlag('WebSocket')->setMethod('message')->match($path, [$server, $frame]);
    }

    public function onClose($server, $fd) {
        $path = (Connections::get($fd))['path'];

        app('route')->setFlag('WebSocket')->setMethod('close')->match($path, [$server, $fd]);

        $this->app->make('event')->trigger('ws.close', [$this, $server, $fd]);

        Connections::del($fd);
    }

    /**
     * 针对连接当前服务进行群发
     */
    public function sendAll($msg)
    {
        foreach ($this->swooleServer->connections as $key => $fd) {
            if ($this->swooleServer->exists($fd)) {
                $this->swooleServer->push($fd, $msg);
            }
        }
    }
}
