<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Lib;

use Swoft\Core\ResultInterface;

/**
 *
 * @method ResultInterface deferGetConnect(int $room_id)
 */
interface LiveInterface
{

    /**
     * @param string $id
     *
     * @return array
     */
    public function getConnect(int $room_id);

}