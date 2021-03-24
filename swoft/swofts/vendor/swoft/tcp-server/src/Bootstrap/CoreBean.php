<?php

namespace Swoft\Tcp\Server\Bootstrap;

use Swoft\Bean\Annotation\BootBean;
use Swoft\Core\BootBeanInterface;
use Swoft\Tcp\Server\TcpServiceDispatcher;

/**
 * The core bean of service
 *
 * @BootBean()
 */
class CoreBean implements BootBeanInterface
{
    /**
     * @return array
     */
    public function beans()
    {
        return [
            'TcpServiceDispatcher' => [
                'class' => TcpServiceDispatcher::class,
            ]
        ];
    }
}