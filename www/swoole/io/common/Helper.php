<?php

function dd($masage){
    if (!is_string($masage)) {
        var_dump($masage).PHP_EOL;
    } else {
        echo "==== >>>> : ".$masage.PHP_EOL;
    }
}

// 发送信息
function send($conn, $data)
{
    is_cli

    if($flag){
        $response = $data;
    }else{
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/html;charset=UTF-8\r\n";
        $response .= "Connection: keep-alive\r\n";
        $response .= "Content-length: ".strlen($data)."\r\n\r\n";
        $response .= $data;
    }
    fwrite($conn, $response);
}