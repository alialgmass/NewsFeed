<?php

return [
    'name' => 'Gateway',

    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'X-Authorization', 'Authorization', 'X-Webhook-Signature', 'X-Webhook-Event', 'X-Requested-With', 'Accept'],
        'exposed_headers' => ['X-RateLimit-Remaining', 'X-RateLimit-Reset'],
        'max_age' => 86400,
        'supports_credentials' => true,
    ],

    'rate_limiting' => [
        'default' => '60,1',
        'webhook' => '30,1',
        'tiers' => [
            'basic' => '60,1',
            'pro' => '300,1',
            'enterprise' => '1000,1',
            'unlimited' => '0,1',
        ],
    ],

    'webhook' => [
        'signature_header' => 'X-Webhook-Signature',
        'event_header' => 'X-Webhook-Event',
        'tolerance' => 300,
    ],

    'api_key' => [
        'header' => 'X-Authorization',
        'length' => 64,
    ],
];
