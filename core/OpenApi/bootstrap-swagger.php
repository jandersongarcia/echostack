<?php

use OpenApi\Generator;

$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname($envPath));
    $dotenv->safeLoad();
}

// Ensure annotation file is loaded
require_once __DIR__ . '/../OpenApi/ApiDefinition.php';

$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/') . '/v1';

return [
    'servers' => [
        [
            'url' => $appUrl,
            'description' => 'Local server',
        ]
    ]
];
