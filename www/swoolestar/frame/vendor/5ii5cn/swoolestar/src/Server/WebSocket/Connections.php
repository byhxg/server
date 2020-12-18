<?php
namespace SwooleStar\Server\WebSocket;

class Connections
{
    /**
     * 记录用户的连接
     * [
     *    fd => [
     *        'path' => xxx,
     *        'xxx' => ooo
     *    ]
     * ]
     * @var array
     */
    protected static $connections = [];

    public static function init($fd, $request)
    {
        self::$connections[$fd]['path'] = $request->server['path_info'];
        self::$connections[$fd]['request'] = $request;
        dd('用户连接信息'.json_encode(self::$connections[$fd],JSON_UNESCAPED_UNICODE),__CLASS__.'|'.__FUNCTION__.'|'.__LINE__);
    }

    public static function get($fd)
    {
        return self::$connections[$fd];
    }

    public static function del($fd)
    {
        unset(self::$connections[$fd]);
    }
}
