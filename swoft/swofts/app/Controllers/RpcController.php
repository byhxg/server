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

use App\Lib\DemoInterface;
use App\Lib\LiveInterface;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Server\Bean\Annotation\Controller;
use Swoft\Http\Server\Bean\Annotation\RequestMapping;
use Swoft\Rpc\Client\Bean\Annotation\Reference;

/**
 * rpc controller test
 *
 * @Controller(prefix="rpc")
 */
class RpcController
{

    /**
     * @Reference(name="user", fallback="demoFallback")
     *
     * @var DemoInterface
     */
    private $demoService;

    /**
     * @Reference(name="user", version="1.0.1")
     *
     * @var DemoInterface
     */
    private $demoServiceV2;

    /**
     * @Reference("user")
     * @var \App\Lib\MdDemoInterface
     */
    private $mdDemoService;

    /**
     * @Inject()
     * @var \App\Models\Logic\UserLogic
     */
    private $logic;



    /**
     * @Reference(name="live", fallback="liveFallback", version="1.0.2")
     *
     * @var LiveInterface
     */
    private $liveService;

    /**
     *
     * @return array
     */
    public function live()
    {
        $result1  = $this->liveService->getConnect(1001);
        return [
            $result1,
        ];
    }

    /**
     * @return array
     */
    public function fallback()
    {
        $result1  = $this->demoService->getUser('11');
        $result2  = $this->demoService->getUsers(['1','2']);
        $result3  = $this->demoService->getUserByCond(1, 2, 'boy', 1.6);

        return [
            $result1,
            $result2,
            $result3,
        ];
    }

    /**
     * @return array
     */
    public function deferFallback()
    {

        $time=time();
        $result1  = $this->demoService->deferGetUser('11')->getResult();
        $result2  = $this->demoService->deferGetUsers(['1','2'])->getResult();
        $result3  = $this->demoService->deferGetUserByCond(1, 2, 'boy', 1.6)->getResult();
       echo  time()-$time;
        return [
            'defer',
            $result1,
            $result2,
            $result3,
        ];



    }
    /**
     * @RequestMapping(route="call")
     * @return array
     */
    public function call()
    {
        $time=time();
        $version  = $this->demoService->deferGetUser('11'); //等待的时候会让出执行权限
        $version2 = $this->demoServiceV2->deferGetUser('11');
        echo  time()-$time;
        return [
            'version'  => $version,
            'version2' => $version2,
        ];
    }
    /**
     * Defer call
     */
    public function defer(){
        $time=time();
        //max（4|2|2）
        $defer3 = $this->demoServiceV2->deferGetUserByCond(1, 2, 'boy', 1.6);
        $defer2 = $this->demoServiceV2->deferGetUsers(['2', '3']);
        $defer1 = $this->demoService->deferGetUser('123');
        $result1 = $defer1->getResult(); //延迟收包
        $result2 = $defer2->getResult();
        $result3 = $defer3->getResult();
        echo time()-$time;
        return [$result1, $result2, $result3];
    }

    public function deferError()
    {
        $defer1 = $this->demoService->deferGetUser('123');
        return ['error'];
    }

    public function beanCall()
    {
        return [
            $this->logic->rpcCall()
        ];
    }

    /**
     * @RequestMapping("validate")
     */
    public function validate()
    {
        $result = $this->demoService->getUserByCond(1, 2, 'boy', '4');

        return ['validator', $result];
    }


    /**
     * @RequestMapping("pm")
     */
    public function parentMiddleware()
    {
        $result = $this->mdDemoService->parentMiddleware();

        return ['parentMiddleware', $result];
    }

    /**
     * @RequestMapping("fm")
     */
    public function funcMiddleware()
    {
        $result = $this->mdDemoService->funcMiddleware();

        return ['funcMiddleware', $result];
    }
}