<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Authorization', 'Authorization', 'X-Webhook-Signature', 'X-Webhook-Event', 'X-Requested-With', 'Accept'],

    'exposed_headers' => ['X-RateLimit-Remaining', 'X-RateLimit-Reset'],

    'max_age' => 86400,

    'supports_credentials' => true,

];
