<?php
return [
    'http'=>[
        'host' => '192.168.0.88',
        'port' => 9502,
        'swoole' => [
            "task_worker_num" => 0,
        ]
    ],
    "rpc" => [
        'tcpable'=>1,                      //是否开启tcp监听
        "host" => "127.0.0.1",            //本机ip
        "port" => "8000",
        'swoole' => [                        //swoole配置
            "task_worker_num" => 2,
            // 'daemonize' => 0,             //是否开启守护进程
        ],
    ],
    'ws' => [
        'host' => '192.168.0.88',                //ws服务
        'port' => 9502,                      //监听端口
        'enable_http' => true,             //是否开启http服务
        'swoole' => [                       //swoole配置
            "task_worker_num" => 0,
            // 'daemonize' => 0,             //是否开启守护进程
        ],
        'is_handshake' => true
    ],
    //分发服务
    'route' => [
        'server' => [
            'host' => "192.168.0.88",
            'port' => 9500,
        ],
        'jwt' => [
            'key' => 'swocloud',
            'alg' => [
                'HS256'
            ]
        ]
    ],
];
