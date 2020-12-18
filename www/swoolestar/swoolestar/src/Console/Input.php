<?php
namespace SwooleStar\Console;

class Input
{
    static public $body;

    public static function put(){
        echo self::$body;
    }

    public static function info($message, $description = null)
    {
        $str = "↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓【".microtime(true).'】'.$description.PHP_EOL;
        if (\is_array($message)) {
            $str .= \var_export($message, true);
            //$str .= \json_encode($message, JSON_UNESCAPED_UNICODE);
        } else if (\is_string($message)) {
            $str .=  $message;
        } else {
            $str .=  var_dump($message);
            //$str .= \json_encode($message, JSON_UNESCAPED_UNICODE);
        }
        echo $str.PHP_EOL;
    }
}
