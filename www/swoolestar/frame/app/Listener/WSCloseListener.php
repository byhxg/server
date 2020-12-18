<?php
namespace App\Listener;

use SwooleStar\Server\WebSocket\Connections;
use SwooleStar\Server\WebSocket\WebSocketServer;
use SwooleStar\Event\Listener;
use Firebase\JWT\JWT;

class WSCloseListener extends Listener
{
    protected $name = 'ws.close';

    public function handler(WebSocketServer $SwooleStarServer = null, $swooleServer  = null, $fd = null)
    {
        // 获取删除的用户 -> jwt -> token  -> header -> request
        $request = Connections::get($fd)['request'];
        $token = $request->header['sec-websocket-protocol'];

        $config = $this->app->make('config');
        $key = $config->get('server.route.jwt.key');
        // 1. 进行jwt验证
        $jwt = JWT::decode($token, $key, $config->get('server.route.jwt.alg'));
        // 删除
        $SwooleStarServer->getRedis()->hDel($key, $jwt->data->uid);
    }


}
