<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Services;

use App\Lib\DemoInterface;
use App\Lib\LiveInterface;
use Swoft\Bean\Annotation\Enum;
use Swoft\Bean\Annotation\Floats;
use Swoft\Bean\Annotation\Number;
use Swoft\Bean\Annotation\Strings;
use Swoft\Rpc\Server\Bean\Annotation\Service;
use Swoft\Core\ResultInterface;
use Swoole\Mysql\Exception;

/**
 * Demo servcie
 *
 * @method ResultInterface deferGetConnect(int $room_id)
 *
 * @Service(version="1.0.2")  //定义版本
 */
class LiveService implements LiveInterface
{

    public function getConnect(int $room_id)
    {
         throw  new Exception('123456');
        //返回当前这台机器
         return ['118.24.109.254:9504'];
    }

}