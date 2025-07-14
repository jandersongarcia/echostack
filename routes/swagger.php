<?php

$swaggerBase = $_ENV['SWAGGER_ROUTE'] ?? '/docs';
$swaggerJsonUri = $swaggerBase.'/swagger.json';

$router->map('GET', $swaggerJsonUri, function () {
    $env = $_ENV['APP_ENV'] ?? 'production';
    $enabled = filter_var($_ENV['SWAGGER_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $accessKey = $_ENV['SWAGGER_ACCESS_KEY'] ?? null;
    $providedKey = $_SERVER['HTTP_X_SWAGGER_KEY'] ?? '';

    if (!$enabled || $env === 'production') {
        http_response_code(403);
        echo json_encode(['error' => 'Swagger access is disabled.']);
        return;
    }

    if ($accessKey && $providedKey !== $accessKey) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $swaggerPath = __DIR__ . '/../core/OpenApi/openapi.json';

    if (!file_exists($swaggerPath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Documentation not found.']);
        return;
    }

    header('Content-Type: application/json');
    readfile($swaggerPath);
});