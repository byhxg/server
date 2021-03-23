<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Pool;

use App\Pool\Config\LivePoolConfig;
use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Pool;
use Swoft\Rpc\Client\Pool\ServicePool;

/**
 * the pool of user service
 *
 * @Pool(name="live")
 */
class LiveServicePool extends ServicePool
{
    /**
     * @Inject()
     *
     * @var LivePoolConfig
     */
    protected $poolConfig;
}