<?php
namespace SwooleStar\Foundation;
use SwooleStar\Server\Http\HttpServer;
use SwooleStar\Server\WebSocket\WebSocketServer;
use SwooleStar\Container\Container;
use SwooleStar\Routes\Route;

/**
 *
 */
class Application extends Container
{
    protected $basePath = "";

    public function __construct( $path= null){
        if (!empty($path)) {
            //$this->setBasePath($path);
        }
        $this->registerBaseBindings();
        $this->init();
    }

    public function run($arg){

        $server = null;
        if(!isset($arg[1])){
            $this->startError();
        }
        switch ($arg[1]) {
            case 'http.start':
                $server = new HttpServer($this);
                break;
            case 'ws.start':
                $server = new WebSocketServer($this);
                break;
            default:
                $this->startError();
        }
        // dd($arg[1], '$argv');
        // // php bin/swostar http:start
        //
        //
        // // php bin/swostar ws:start
        // $server = new WebSocketServer($this);
        //
        $server->watchFile(true);
        $server->start();
    }

    /**
     * 注册框架事件
     * 六星教育 @shineyork老师
     * @return Event
     */
    public function registerEvent()
    {
        $event = new \SwooleStar\Event\Event();

        $files = scandir(APP_PATH.'/app/Listener');

        // 2. 读取文件信息
        foreach ($files as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            // $file => StartListener.php

            $class = 'App\\Listener\\'.\explode('.', $file)[0];

            if (\class_exists($class)) {
                $listener = new $class($this);
                $event->register($listener->getName(), [$listener, 'handler']);
            }
        }

        return $event;
    }

    public function setBasePath($path)
    {
        $this->basePath = \rtrim($path, '\/');
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    //ioc绑定
    public function registerBaseBindings(){
        self::setInstance($this);
        $binds = [
            // 标识  ， 对象
            'config' => function(){
                return new \SwooleStar\Config\Config();
            },
            'httpRequest' => function(){
                return new \SwooleStar\Message\Http\Request();
            },
        ];
        foreach ($binds as $key => $value) {
            $this->bind($key, $value);
        }
    }

    public function init()
    {
        $this->bind('route', Route::getInstance()->registerRoute());
        $this->bind('event', $this->registerEvent());
        $this->swooolestar_welcome();
    }


    private function swooolestar_welcome(){
        $txt = "
   _____                            __         _____   __
  / ___/ _      __  ____   ____    / /  ___   / ___/  / /_  ____ _   _____
  \\__ \\ | | /| / / / __ \ / __ \\  / /  / _ \\  \\__ \\  / __/ / __ `/  / ___/
 ___/ / | |/ |/ / / /_/ // /_/ / / /  /  __/ ___/ / / /_  / /_/ /  / /
/____/  |__/|__/  \\____/ \\____/ /_/   \\___/ /____/  \\__/  \\__,_/  /_/

    ";
        echo $txt;
    }

    public function startError(){
        dd('命令错误;http.start | ws.start');exit;
    }
}
