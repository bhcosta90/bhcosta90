<?php

declare(strict_types = 1);

return [
    'defaults' => [
        'supervisor-1' => [
            'connection'          => 'redis',
            'queue'               => ['default'],
            'balance'             => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses'        => 1,
            'maxTime'             => 0,
            'maxJobs'             => 0,
            'memory'              => 128,
            'tries'               => 1,
            'timeout'             => 60,
            'nice'                => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'maxProcesses'    => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 3,
            ],
        ],
    ],
];
