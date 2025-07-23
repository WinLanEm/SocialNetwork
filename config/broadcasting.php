<?php
return [
    'default' => env('BROADCAST_DRIVER', 'pusher'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'host' => 'localhost',
            'port' => 443,
            'scheme' => 'https',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
                'encrypted' => true,
                'allowedOrigins' => [
                    'https://localhost',
                    'http://localhost',
                    'http://localhost:80',
                    'http://localhost:8876',
                    'https://localhost:443',
                    'https://js.pusher.com',
                    'wss://ws-eu.pusher.com',
                    'http://localhost:5173',
                    'https://localhost:5173',
                    'https://172.18.0.8:5173',
                ],
            ],
        ],
    ],
];
