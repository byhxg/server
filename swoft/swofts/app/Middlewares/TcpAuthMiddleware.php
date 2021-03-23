<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Middlewares;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\Auth\Constants\AuthConstants;
use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\Inject;
use Swoft\Http\Message\Middleware\MiddlewareInterface;
use Swoft\App;
use Swoft\Auth\Exception\AuthException;
use Swoft\Auth\Helper\ErrorCode;
use Swoft\Auth\Mapping\AuthorizationParserInterface;
use Swoft\Tcp\Server\Middleware\PackerMiddleware;

/**
 * @Bean()
 * @uses      ActionTestMiddleware
 * @version   2017年11月16日
 * @author    huangzhhui <huangzhwork@gmail.com>
 * @copyright Copyright 2010-2017 Swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class TcpAuthMiddleware implements MiddlewareInterface
{

    /**
     * @Inject()
     * @var \Swoft\Redis\Redis
     */
      private  $redis;

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $server=$request->getAttribute(PackerMiddleware::ATTRIBUTE_SERVER);
        $fd=$request->getAttribute(PackerMiddleware::ATTRIBUTE_FD);
        $data=$request->getAttribute(PackerMiddleware::ATTRIBUTE_DATA);
        //close掉
        try{
            //TODO 默认组件如果没有携带头信息 Authorization: Bearer 是不会验证
            //header携带头信息 Authorization: Bearer xxxxxxxxxxxxxx
            $parser = App::getBean(AuthorizationParserInterface::class);
            if (!$parser instanceof AuthorizationParserInterface) {
                throw new AuthException(ErrorCode::POST_DATA_NOT_PROVIDED, 'AuthorizationParser should implement Swoft\Auth\Mapping\AuthorizationParserInterface');
            }
            /*
             * todo 将协程redis,变成同步
             *  同步redis
             */
            if(!$this->redis->exists('live_info_'.$fd)) {
                //验证是根据tcp客户端发送的数据验证，在第一次请求时候匹配token
                if (preg_match('/\((.*?)\)/', $data, $match)) {
                    //验证
                    $token = explode("=", $match[1])[1];
                    //手动添加头信息
                    $request= $request->withAddedHeader(AuthConstants::HEADER_KEY, 'Bearer '.$token);
                    $request = $parser->parse($request); //验证token
                    $this->redis->sAdd('live_info_'.$fd,'auth');
                    if(strstr($data,'FLV')){
                        //视频头信息
                        $this->redis->sAdd('live_info_'.$fd,$data);
                        $room_id=json_decode(base64_decode(explode('.',$token)[1]),true)['data']['id'];
                        $this->redis->sAdd('live_info_'.$fd,$room_id);
                        var_dump($this->redis->SMEMBERS('live_info_'.$fd));
                        //auth中间件
                    }
                } else {
                    throw new \Exception('请正确携带token');
                }
            }

        }catch (\Exception $e){
            var_dump('tcp认证抛出',$e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode());
            $server->close($fd); //swoole->close
        }
        $response = $handler->handle($request);
        return $response;
    }
}