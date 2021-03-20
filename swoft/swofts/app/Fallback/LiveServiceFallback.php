<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Fallback;

use App\Lib\DemoInterface;
use App\Lib\LiveInterface;
use Swoft\Sg\Bean\Annotation\Fallback;
use Swoft\Core\ResultInterface;

/**
 * Fallback demo
 *
 * @Fallback("liveFallback")
 * @method ResultInterface deferGetConnect(int $room_id)
 */
class LiveServiceFallback implements LiveInterface
{
    public function getConnect(int $room_id)
    {
        return '直播服务开小差了，请稍后再试';
    }

}