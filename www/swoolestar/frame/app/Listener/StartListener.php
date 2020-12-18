<?php
namespace App\Listener;

use SwooleStar\Event\Listener;
use Swoole\Coroutine;

class StartListener extends Listener
{
    protected $name = 'start';

    public function handler($SwooleStarServer = null, $swooleServer  = null)
    {
        dd('内部监听',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        $config = $this->app->make('config');
        Coroutine::create(function() use ($SwooleStarServer, $config){
            $cli = new \Swoole\Coroutine\Http\Client($config->get('server.route.server.host'), $config->get('server.route.server.port'));
            $ret = $cli->upgrade("/"); //升级的websockt
            if ($ret) {
                $data=[
                    'method'     =>'register', //方法
                    'serviceName'=>'IM1',
                    'ip'         => $SwooleStarServer->getHost(),
                    'port'       => $SwooleStarServer->getPort()
                ];
                dd($data,__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
                $cli->push(json_encode($data));
                //心跳处理
                swoole_timer_tick(3000, function () use( $cli){
                    if($cli->errCode==0){
                        $cli->push('',WEBSOCKET_OPCODE_PING); //
                    }
                });
            }
        });

    }
}
