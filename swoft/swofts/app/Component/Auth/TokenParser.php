<?php
/**
 * Created by PhpStorm.
 * User: sixstar
 * Date: 2018/8/4
 * Time: 21:43
 */

namespace App\Component\Auth;


use Firebase\JWT\JWT;
use Swoft\App;
use Swoft\Auth\Parser\JWTTokenParser;

class TokenParser extends  JWTTokenParser
{
    protected $secret;
    protected $algorithm;
     public  function __construct()
     {
         //
     }
    public function decode($token)
    {
        $config=App::getProperties()['auth']['jwt'];
        $secret=$config['secret'];
        $algorithm=$config['algorithm'];
        return JWT::decode($token,$secret,[$algorithm]);
    }

}