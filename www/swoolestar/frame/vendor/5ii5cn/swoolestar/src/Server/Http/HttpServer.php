<?php
namespace SwooleStar\Server\Http;

use SwooleStar\Server\Server;
use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use SwooleStar\Message\Http\Request as HttpRequest;

class HttpServer extends Server
{
    public function createServer()
    {
        $this->swooleServer = new SwooleServer('0.0.0.0', $this->port);
    }

    /**
     * 配置服务
     */
    public function initSetting()
    {
        $config = app('config');
        $this->port = $config->get('server.http.port');
        $this->host = $config->get('server.http.host');
        $this->config = $config->get('server.http.swoole');
    }


    protected function initEvent(){
        $this->setEvent('sub', [
            'request' => 'onRequest',
        ]);
    }

    // onRequest
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        $uri = $request->server['request_uri'];
        if ($uri == '/favicon.ico') {
            $response->status(404);
            $response->end('');
            return  false;
        }
        $httpRequest = HttpRequest::init($request);
//        dd($httpRequest->getMethod(), "Method");
//        dd($httpRequest->getUriPath(), "UriPath");
//        $response->end("<h1>Hello swostar</h1>");
        // 执行控制器的方法
        $return = app('route')->setFlag('Http')->setMethod($httpRequest->getMethod())->match($httpRequest->getUriPath());
        $response->end($return);
    }
}
