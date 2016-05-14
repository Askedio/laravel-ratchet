<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Ratchet Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can define the default settings for Laravel Ratchet.
    |
    */

    'class'           => \Askedio\LaravelRatchet\RatchetServerExample::class,
    'host'            => '0.0.0.0',
    'port'            => '9090',
    'connectionLimit' => false,
    'throttle'        => [
                            'onOpen' => '5:1',
                            'onMessage' => '20:1',
                         ],
    'abortOnMessageThrottle' => false,
    'blackList'       => collect([]),
];
