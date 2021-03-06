<?php

$action = $argv[1] ?? 'ws';

goto ws;

ws:
//创建WebSocket Server对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9508);
//监听WebSocket连接打开事件
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});
//监听WebSocket消息事件
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});
//监听WebSocket连接关闭事件
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});
$ws->start();
exit;

#####################################
tcp:
//tcp 创建Server对象，监听 127.0.0.1:9501 端口
$server = new Swoole\Server('127.0.0.1', 9508);
//监听连接进入事件
$server->on('Connect', function ($server, $fd) {
    echo "Client: Connect.\n";
});
//监听数据接收事件
$server->on('Receive', function ($server, $fd, $from_id, $data) {
    $server->send($fd, "Server: " . $data);
});
//监听连接关闭事件
$server->on('Close', function ($server, $fd) {
    echo "Client: Close.\n";
});
//启动服务器
$server->start();exit;

#####################################
udp:
$server = new Swoole\Server('127.0.0.1', 9508, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
//监听数据接收事件
$server->on('Packet', function ($server, $data, $clientInfo) {
    var_dump($clientInfo);
    $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server：' . $data);
});
//启动服务器
$server->start();exit;

#####################################
http:
$http = new Swoole\Http\Server('0.0.0.0', 9508);
$http->on('request', function ($request, $response) {
    if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
    }
    var_dump($request->get, $request->post);
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
//    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
//    //根据 $controller, $action 映射到不同的控制器类和方法
//    (new $controller)->$action($request, $response);
});
$http->start();exit;

#####################################
mqtt:
function decodeValue($data)
{
    return 256 * ord($data[0]) + ord($data[1]);
}

function decodeString($data)
{
    $length = decodeValue($data);
    return substr($data, 2, $length);
}

function mqttGetHeader($data)
{
    $byte = ord($data[0]);

    $header['type']   = ($byte & 0xF0) >> 4;
    $header['dup']    = ($byte & 0x08) >> 3;
    $header['qos']    = ($byte & 0x06) >> 1;
    $header['retain'] = $byte & 0x01;

    return $header;
}

function eventConnect($header, $data)
{
    $connect_info['protocol_name'] = decodeString($data);
    $offset                        = strlen($connect_info['protocol_name']) + 2;

    $connect_info['version'] = ord(substr($data, $offset, 1));
    $offset                  += 1;

    $byte                       = ord($data[$offset]);
    $connect_info['willRetain'] = ($byte & 0x20 == 0x20);
    $connect_info['willQos']    = ($byte & 0x18 >> 3);
    $connect_info['willFlag']   = ($byte & 0x04 == 0x04);
    $connect_info['cleanStart'] = ($byte & 0x02 == 0x02);
    $offset                     += 1;

    $connect_info['keepalive'] = decodeValue(substr($data, $offset, 2));
    $offset                    += 2;
    $connect_info['clientId']  = decodeString(substr($data, $offset));
    return $connect_info;
}

$server = new Swoole\Server("127.0.0.1", 9501, SWOOLE_BASE);
$server->set([
                 'open_mqtt_protocol' => 1, // 启用 mqtt 协议
                 'worker_num'         => 1,
             ]);

$server->on('connect', function ($server, $fd) {
    echo "Client:Connect.\n";
});
$server->on('receive', function ($server, $fd, $from_id, $data) {
    $header = mqttGetHeader($data);
    var_dump($header);
    if ($header['type'] == 1) {
        $resp = chr(32) . chr(2) . chr(0) . chr(0);
        eventConnect($header, substr($data, 2));
        $server->send($fd, $resp);
    } elseif ($header['type'] == 3) {
        $offset = 2;
        $topic  = decodeString(substr($data, $offset));
        $offset += strlen($topic) + 2;
        $msg    = substr($data, $offset);
        echo "client msg: $topic\n----------\n$msg\n";
        //file_put_contents(__DIR__.'/data.log', $data);
    }
    echo "received length=" . strlen($data) . "\n";
});
$server->on('close', function ($server, $fd) {
    echo "Client: Close.\n";
});
$server->start();exit;

#####################################
task:
$serv = new Swoole\Server("127.0.0.1", 9501);
//设置异步任务的工作进程数量
$serv->set(array('task_worker_num' => 4));
//此回调函数在worker进程中执行
$serv->on('receive', function($serv, $fd, $from_id, $data) {
    //投递异步任务
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id=$task_id\n";
});
//处理异步任务(此回调函数在task进程中执行)
$serv->on('task', function ($serv, $task_id, $from_id, $data) {
    echo "New AsyncTask[id=$task_id]".PHP_EOL;
    //返回任务执行的结果
    $serv->finish("$data -> OK");
});
//处理异步任务的结果(此回调函数在worker进程中执行)
$serv->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});
$serv->start();exit;
#####################################
Swoole\Runtime::enableCoroutine();// 此行代码后，文件操作，sleep，Mysqli，PDO，streams等都变成异步IO，见'一键协程化'章节
$s = microtime(true);
// Co\run()见'协程容器'章节
Co\run(function() {
    // i just want to sleep...
    for ($c = 100; $c--;) {
        go(function () {
            for ($n = 100; $n--;) {
                usleep(1000);
            }
        });
    }

    // 10k file read and write
    for ($c = 100; $c--;) {
        go(function () use ($c) {
            $tmp_filename = "/tmp/test-{$c}.php";
            for ($n = 100; $n--;) {
                $self = file_get_contents(__FILE__);
                file_put_contents($tmp_filename, $self);
                assert(file_get_contents($tmp_filename) === $self);
            }
            unlink($tmp_filename);
        });
    }

    // 10k pdo and mysqli read
    for ($c = 50; $c--;) {
        go(function () {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=test;charset=utf8', 'root', 'root');
            $statement = $pdo->prepare('SELECT * FROM `user`');
            for ($n = 100; $n--;) {
                $statement->execute();
                assert(count($statement->fetchAll()) > 0);
            }
        });
    }
    for ($c = 50; $c--;) {
        go(function () {
            $mysqli = new Mysqli('127.0.0.1', 'root', 'root', 'test');
            $statement = $mysqli->prepare('SELECT `id` FROM `user`');
            for ($n = 100; $n--;) {
                $statement->bind_result($id);
                $statement->execute();
                $statement->fetch();
                assert($id > 0);
            }
        });
    }

    // php_stream tcp server & client with 12.8k requests in single process
    function tcp_pack(string $data): string
    {
        return pack('n', strlen($data)) . $data;
    }

    function tcp_length(string $head): int
    {
        return unpack('n', $head)[1];
    }

    go(function () {
        $ctx = stream_context_create(['socket' => ['so_reuseaddr' => true, 'backlog' => 128]]);
        $socket = stream_socket_server(
            'tcp://0.0.0.0:9502',
            $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $ctx
        );
        if (!$socket) {
            echo "$errstr ($errno)\n";
        } else {
            $i = 0;
            while ($conn = stream_socket_accept($socket, 1)) {
                stream_set_timeout($conn, 5);
                for ($n = 100; $n--;) {
                    $data = fread($conn, tcp_length(fread($conn, 2)));
                    assert($data === "Hello Swoole Server #{$n}!");
                    fwrite($conn, tcp_pack("Hello Swoole Client #{$n}!"));
                }
                if (++$i === 128) {
                    fclose($socket);
                    break;
                }
            }
        }
    });
    for ($c = 128; $c--;) {
        go(function () {
            $fp = stream_socket_client("tcp://127.0.0.1:9502", $errno, $errstr, 1);
            if (!$fp) {
                echo "$errstr ($errno)\n";
            } else {
                stream_set_timeout($fp, 5);
                for ($n = 100; $n--;) {
                    fwrite($fp, tcp_pack("Hello Swoole Server #{$n}!"));
                    $data = fread($fp, tcp_length(fread($fp, 2)));
                    assert($data === "Hello Swoole Client #{$n}!");
                }
                fclose($fp);
            }
        });
    }

    // udp server & client with 12.8k requests in single process
    go(function () {
        $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
        $socket->bind('127.0.0.1', 9503);
        $client_map = [];
        for ($c = 128; $c--;) {
            for ($n = 0; $n < 100; $n++) {
                $recv = $socket->recvfrom($peer);
                $client_uid = "{$peer['address']}:{$peer['port']}";
                $id = $client_map[$client_uid] = ($client_map[$client_uid] ?? -1) + 1;
                assert($recv === "Client: Hello #{$id}!");
                $socket->sendto($peer['address'], $peer['port'], "Server: Hello #{$id}!");
            }
        }
        $socket->close();
    });
    for ($c = 128; $c--;) {
        go(function () {
            $fp = stream_socket_client("udp://127.0.0.1:9503", $errno, $errstr, 1);
            if (!$fp) {
                echo "$errstr ($errno)\n";
            } else {
                for ($n = 0; $n < 100; $n++) {
                    fwrite($fp, "Client: Hello #{$n}!");
                    $recv = fread($fp, 1024);
                    list($address, $port) = explode(':', (stream_socket_get_name($fp, true)));
                    assert($address === '127.0.0.1' && (int)$port === 9503);
                    assert($recv === "Server: Hello #{$n}!");
                }
                fclose($fp);
            }
        });
    }
});
echo 'use ' . (microtime(true) - $s) . ' s';