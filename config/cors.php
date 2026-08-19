<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:5175',
        'http://localhost:5176',
        'http://localhost:3000',
        'http://localhost:3001',
        'http://localhost:3002',
        'http://localhost:3003',
        env('FRONTEND_URL', '*'),
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Authorization',
        'Content-Type',
        'Accept',
        'X-Requested-With',
        'X-Market-Id',
        'Origin',
        'Cache-Control',
        'X-Requested-With',
    ],
    'exposed_headers' => [],
    'max_age' => 60,
    'supports_credentials' => false,
];
