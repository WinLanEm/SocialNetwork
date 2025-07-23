<?php
return [
    'paths' => [
        'api/*',
        'broadcasting/auth', // <- это важно для приватных каналов
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://localhost',
        'http://localhost',
        'https://localhost:8876',
        'http://localhost:8876',
        'http://localhost:5743',
        'https://localhost:5743',
        'https://localhost:443',
        'http://localhost:80',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
