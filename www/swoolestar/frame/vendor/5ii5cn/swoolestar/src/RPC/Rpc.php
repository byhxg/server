<?php
namespace SwooleStar\RPC;

use SwooleStar\Console\Input;

use Swoole\Server;

class Rpc
{
    protected $host ;

    protected $port;

    public function __construct(Server $server, $config)
    {
        $listen = $server->listen($config['host'], $config['port'], SWOOLE_SOCK_TCP);
        $listen->set($config['swoole']);
        //设置回掉函数
        $listen->on('connect', [$this, 'connect']);
        $listen->on('receive', [$this, 'receive']);
        $listen->on('close', [$this, 'close']);

        Input::info('tcp监听的地址: '.$config['host'].':'.$config['port'] );
    }

    public function connect($serv, $fd){
        dd('内部 rpc 监听 连接',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
    }

    public function receive($serv, $fd, $from_id, $data) {
        dd('内部 rpc 监听 返回',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        $serv->send($fd, 'Swoole: '.$data);
        $serv->close($fd);
    }

    public function close($serv, $fd) {
        dd('内部 rpc 监听 端口',__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
        echo "Client: Close.\n";
    }
}
