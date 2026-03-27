<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_STORE', 'file'),

    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => env('CACHE_DB_CONNECTION'),
            'lock_connection' => env('CACHE_DB_LOCK_CONNECTION'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('CACHE_REDIS_CONNECTION', 'cache'),
            'lock_connection' => env('CACHE_REDIS_LOCK_CONNECTION', 'default'),
        ],
    ],

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel'), '_') . '_cache_'),
];
