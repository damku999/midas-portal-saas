<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Redis Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all Redis work. Of course, you may use many
    | connections at once using the Database library.
    |
    */

    'default' => env('REDIS_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Redis Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the Redis connections setup for your application.
    | Feel free to add more connections as required.
    |
    */

    'connections' => [

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'read_timeout' => 60,
            'context' => [
                // 'auth' => ['username', 'secret'],
                // 'stream' => ['verify_peer' => false],
            ],
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'read_timeout' => 60,
            'context' => [
                // 'auth' => ['username', 'secret'],
                // 'stream' => ['verify_peer' => false],
            ],
        ],

        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
            'read_timeout' => 60,
            'context' => [
                // 'auth' => ['username', 'secret'],
                // 'stream' => ['verify_peer' => false],
            ],
        ],

        'queue' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
            'read_timeout' => 60,
            'context' => [
                // 'auth' => ['username', 'secret'],
                // 'stream' => ['verify_peer' => false],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Sentinel Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the Redis Sentinel servers used to monitor your
    | Redis instances. These servers should be used for high availability
    | and automatic failover support.
    |
    */

    'sentinel' => [
        'host' => env('REDIS_SENTINEL_HOST', '127.0.0.1'),
        'port' => env('REDIS_SENTINEL_PORT', '26379'),
        'service' => env('REDIS_SENTINEL_SERVICE', 'mymaster'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Cluster Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure Redis clustering for horizontal scaling.
    | When using clustering, the client will automatically distribute
    | keys across multiple Redis nodes for improved performance.
    |
    */

    'clusters' => [

        'default' => [
            [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', '6379'),
                'database' => 0,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),

    /*
    |--------------------------------------------------------------------------
    | Redis Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure Redis specific options for the application.
    | These options will be applied to all Redis connections and can be
    | used to tune performance based on your specific requirements.
    |
    */

    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],

];
