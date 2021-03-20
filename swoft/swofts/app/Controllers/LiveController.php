<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Controllers;

use App\Component\Auth\TokenParser;
use App\Exception\SwoftExceptionHandler;
use App\Middlewares\LiveAuthMiddleware;
use Swoft\App;
use Swoft\Auth\Constants\AuthConstants;
use Swoft\Auth\Mapping\AuthManagerInterface;
use Swoft\Bean\Annotation\Inject;
use Swoft\Core\RequestContext;
use Swoft\Http\Message\Bean\Annotation\Middleware;
use Swoft\Http\Message\Server\Request;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Http\Server\Bean\Annotation\RequestMethod;
use Swoft\Rpc\Server\Rpc\RpcServer;
use Swoft\Tcp\Server\Tcp\TcpServer;
use Swoft\Auth\Mapping\AuthorizationParserInterface;
use Swoole\Mysql\Exception;

// use Swoft\View\Bean\Annotation\View;
// use Swoft\Http\Message\Server\Response;

/**
 * Class LiveController
 * @Controller(prefix="live")
 * @package App\Controllers
 */
class LiveController{
    /**
     * this is a example action. access uri path: live
     * @RequestMapping(route="live", method=RequestMethod::GET)
     * @return array
     */

    /**
     * @Inject()
     * @var \Swoft\Redis\Redis
     */
    private  $redis;

    private   $process;

    public function index(Request $request)
    {

       // $username = $request->getAttribute(AuthConstants::BASIC_USER_NAME) ?? '';
        //$password = $request->getAttribute(AuthConstants::BASIC_PASSWORD) ?? '';
        //生成token
        $username='peter';
        $password='123456';
        if(!$username || !$password){
            return [
                "code"=>ErrorCodes::POST_DATA_NOT_PROVIDED,
                "message"=>"Basic Auth:{username,password}"
            ];
        }
        //从容器当中获取，自定义的manage对象
        $manager = App::getBean(AuthManagerInterface::class);

        /** @var AuthSession $session  自定义的方法*/
        $session = $manager->adminLogin($username,$password);
        $data = [
            'token'=>$session->getToken(),
            'expire'=>$session->getExpirationTime()
        ];
        return $data;
         //return view('live/index');
    }

    public  function connect(Request $request){
            return 3;
    }

    /**
     * @RequestMapping(route="publish")
     * @Middleware(class=LiveAuthMiddleware::class)
     * @param Request $request
     */
    public  function publish(Request $request){

        $data=json_decode($request->raw());
        //token
       // return '{"code":1}'; //测试生效，可以课下自己试试
       // return '{"code":0}';
        //主播停止推流
        //todo 验证自己做,比如token不存在也同样不允许推流
        try{
            $parser = App::getBean(AuthorizationParserInterface::class); //从容器当中加载
            $parser->parse($request); //验证token
            $token=$request->getHeaderLine(AuthConstants::HEADER_KEY);
            //创建子进程，执行ffmpeg转码
            $this->process=new \App\Component\Process\Process($token);

            //client_id|pid|id
            $srs_key='live_srs_'.$data->client_id;
            //主播id
            $id=RequestContext::getContextData()[AuthConstants::AUTH_SESSION ]->getExtendedData()->id;
            //哈希当中添加了
            $this->redis->hset($srs_key,'pid', $this->process->pid);
            $this->redis->hset($srs_key,'aid',$id);

            //当前主播是否开播，直播间状态
            $this->redis->sAdd('live_room_'.$id,'open');


        }catch (\Exception $e){
             //记录错误信息
             //发送通知
             //业务逻辑
            //(new SwoftExceptionHandler())->
            var_dump("异常信息",$e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode());
            return json_encode(['code'=>1]); //没有断开，反复推送
        }
        //允许推流
        return '{"code":0}'; //允许推流
    }

    public  function close(Request $request){
        $client_id=json_decode($request->raw())->client_id;
        //todo 将key定义成常量形式，便于维护
        $srs_key='live_srs_'.$client_id;
        //逻辑层
        $data=$this->redis->HGETALL($srs_key);
         //强制杀掉进程
        //var_dump(exec('kill -9 '.$data[1]));
        var_dump(\swoole\process::kill($data[1], SIGKILL));  //
        //调整主播直播间状态 (业务逻辑)

    }

}
