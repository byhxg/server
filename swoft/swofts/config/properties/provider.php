<?php

/*
 * This file is part of Swoft.
 * (c) Swoft <group@swoft.org>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$ip=swoole_get_local_ip()['eth0'];
$consul=gethostbyname('consul-client');
return [
    'consul' => [
        'address' =>$consul,
        'port'    => 8500,
        'register' => [
            'name'              => 'live',
            'tags'              => [],
            'enableTagOverride' => false,
            'service'           => [
                'address' => $ip,
                'port'   => '8099',
            ],
            'check'             => [
                'name'     => 'live',
                'tcp'      => $ip.':8099',
                'interval' => 10,
                'timeout'  => 1,
            ],
        ],
        'discovery' => [
            'name' => 'live',
            'dc' => 'dc',
            'near' => '',
            'tag' =>'',
            'passing' => true
        ]
    ],
];