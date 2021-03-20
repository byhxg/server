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

use Swoft\Bean\Annotation\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;
use Swoft\WebSocket\Server\Event\WsEvent;
use Swoft\Bean\Annotation\Inject;

/**
 * Event after Tcp request
 * @Listener(WsEvent::ON_MESSAGE)  //消息事件
 */
class MessageListener implements EventHandlerInterface
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

        //合法性验证
        //触发事件
        $params=$event->getParams();
        $frame=$params[1];
        $data=json_decode($frame->data,true); //客户端传过来的fd,区分不同的视频流
        /*当前客户端fd所对应的视频流fd
          connection_fd=>服务端fd
                         是否发送视频头
        */

       // var_dump($this->redis->sIsMember('live_room'.$data['room_id'],'open'));
        if($this->redis->sIsMember('live_room_'.$data['room_id'],'open')){
                //房间绑定用户id
                $this->redis->sAdd('live_room_'.$data['room_id'],$data['uid']);

                //通过用户id得到，得到绑定的房间
               $this->redis->hSet('live_user_'.$data['uid'],$data['room_id'].'_'.$data['uid'],$frame->fd);
//                                                    1 (用户id)        (room_id)1001_1  =>20 (fd)
//                                                    1         1002_1  =>22

    }

        //当前获取哪条视频流当中的数据,保存视频流对应客户端(房间)

        //$this->redis->sAdd('live_'.$data['server_fd'],$frame->fd);


    }
}
