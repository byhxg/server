<?php
namespace App\WebSocket\Controller;

/**
 *
 */
class IndexController
{
    public function open($server, $request)
    {
        dd('控制器',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
    }
    public function message($server, $frame)
    {
        dd('发送消息',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        $server->push($frame->fd, "index message 方法");
    }
    public function close($ser, $fd)
    {
        dd('结束消息',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
    }
}
