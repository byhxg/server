<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Listener;

use Swoft\App;
use Swoft\Bean\Annotation\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\Tcp\Server\Event\TcpServerEvent;
use Swoft\Bean\Annotation\Inject;
use Swoft\WebSocket\Server\Event\WsEvent;

/**
 * Event after Tcp request
 * @Listener(TcpServerEvent::RECEIVE)  //消息事件
 */
class ReceiveListener implements EventHandlerInterface
{

    /**
     * @Inject()
     * @var \Swoft\Redis\Redis
     */
    private  $redis;

    /**
     * @param EventInterface $event
     */


    public function handle(EventInterface $event)
    {
          //触发事件
        $params=$event->getParams();
        $server_fd=$params[0];
        $data=$params[1];
        $server=$params[2];
        $redis=$params[3];

        $info=$redis->SMEMBERS('redis_live_info_'.$server_fd);
        $room_id=unserialize($info[2]);

//        echo "房间id".PHP_EOL;
//        var_dump($room_id);
//        echo "房间id".PHP_EOL;

        //$settings = App::getAppProperties()->get('live'); //获取配置信息

        //需要获取到redis当中,房间里面的fd
        //TODO  修改redis数据获取，注意前缀，注意序列化
        $live=$redis->SMEMBERS('redis_live_room_'.$room_id);

//        echo "房间绑定的用户信息".PHP_EOL;
//        var_dump($live);
//        echo "房间绑定的用户信息".PHP_EOL;

        foreach ($live as $value){
            $value=unserialize($value); //uid=>fd
            //跳过默认的open
            if($value=='open') {
                continue;
            }
//            echo "用户id".PHP_EOL;
//            var_dump($value);
//            echo "用户id".PHP_EOL;

            $fd=unserialize($redis->hget('redis_live_user_'.$value,$room_id.'_'.$value));
//            echo "网络fd".PHP_EOL;
//            var_dump($fd);
//            echo "网络fd".PHP_EOL;

            //判断是否是有效客户端，如果失效，触发清除
            if(!$server->exist($fd)) {
                  App::trigger(WsEvent::ON_CLOSE,null,' ',$fd);
                  continue;
            }
            //视频头信息,
            if($redis->exists('redis_connection_'.$fd)){
                $server->push($fd,$data,WEBSOCKET_OPCODE_BINARY); //二进制
            }else{
                //$header=$redis->get('live_info_'.$fd);
                $header=unserialize($info[1]);
                //当前客户端已经发送，设置已经发送
                $redis->set('redis_connection_'.$fd,'1');
                $server->push($fd,$header.$data,WEBSOCKET_OPCODE_BINARY); //二进制
            }
            //如果当前客户端是第一次连接就发送
        }
    }
}
