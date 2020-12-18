<?php
namespace App\Listener;

use SwooleStar\Server\WebSocket\Connections;
use SwooleStar\Server\WebSocket\WebSocketServer;
use SwooleStar\Event\Listener;

use Swoole\Coroutine\Http\Client;

class WSMessageFrontListener extends Listener
{
    protected $name = 'ws.message.front';

    public function handler(WebSocketServer $SwooleStarServer = null, $swooleServer  = null, $frame = null)
    {
        /*
            消息的格式 -》
            {
                'method' => //执行操作
                'msg' => 消息,
            }
         */
         $data = \json_decode($frame->data, true);
        dd($data,'接受客户端的消息'.__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        $this->{$data['method']}($SwooleStarServer, $swooleServer ,$data, $frame->fd);
    }
    /**
     * 服务器广播
     *
     * 六星教育 @shineyork老师
     * @param  WebSocketServer $SwooleStarServer [description]
     */
    protected function serverBroadcast(WebSocketServer $SwooleStarServer, $swooleServer ,$data, $fd)
    {
        $config = $this->app->make('config');

        $cli = new Client($config->get('server.route.server.host'), $config->get('server.route.server.port'));
        if ($cli->upgrade('/')) $cli->push(\json_encode([
            'method' => 'routeBroadcast',
            'msg' => $data['msg']
        ]));

    }

    /**
     * 接收Route服务器的广播信息
     *
     * 六星教育 @shineyork老师
     * @param  WebSocketServer $SwooleStarServer [description]
     */
    protected function routeBroadcast(WebSocketServer $swoStarServer, $swooleServer ,$data, $fd)
    {
        $dataAck = [
            'method' => 'ack',
            'msg_id' => $data['msg_id']
        ];
        dd($dataAck,__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        $swooleServer->push($fd, \json_encode($dataAck));
        // dd($data, 'server 中的 routeBroadcast');
        $swoStarServer->sendAll(json_encode($data['data']));
    }

    public function ack()
    {
        // code...
    }


    /**
     * 接收客户端私聊的信息
     *
     * 六星教育 @shineyork老师
     * @param  WebSocketServer $SwooleStarServer [description]
     */
    protected function privateChat(WebSocketServer $SwooleStarServer, $swooleServer ,$data, $fd)
    {
        // 1. 获取私聊的id
        $clientId = $data['clientId'];
        // 2. 从redis中获取对应的服务器信息
        $clientIMServerInfoJson = $SwooleStarServer->getRedis()->hGet($this->app->make('config')->get('server.route.jwt.key'), $clientId);
        dd('接收方的服务器信息  '.$clientIMServerInfoJson ,__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        $clientIMServerInfo = json_decode($clientIMServerInfoJson, true);
        // 3. 指定发送
        $request = Connections::get($fd)['request'];
        $token = $request->header['sec-websocket-protocol'];
        // $url = 0.0.0.0:9000
        $clientIMServerUrl = explode(":", $clientIMServerInfo['serverUrl']);
        $SwooleStarServer->send($clientIMServerUrl[0], $clientIMServerUrl[1], [
            'method' => 'forwarding',
            'msg' => $data['msg'],
            'fd' => $clientIMServerInfo['fd']
        ], [
            'sec-websocket-protocol' => $token
        ]);
    }
    /**
     * 转发私聊信息
     *
     * 六星教育 @shineyork老师
     * @param  WebSocketServer $SwooleStarServer [description]
     */
    protected function forwarding(WebSocketServer $SwooleStarServer, $swooleServer ,$data, $fd)
    {
        $swooleServer->push($data['fd'], json_encode(['msg' => $data['msg']]));
    }
}
