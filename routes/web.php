<?php

use App\Controllers\HealthController;
use Symfony\Component\HttpFoundation\Request;
use Core\Utils\SystemInfo;

$router->map('GET', '/', function() {
    echo SystemInfo::fullSignature();
});

// Healthcheck
$router->map('GET', '/health', function() use ($database, $logger) {
    $controller = new HealthController($database, $logger);
    return $controller->check();
});

// Rotas automáticas para o CRUD de users
$router->map('GET', '/v1/users', 'Src\\Controllers\\UserController#index');
$router->map('GET', '/v1/users/[i:id]', 'Src\\Controllers\\UserController#show');
$router->map('POST', '/v1/users', 'Src\\Controllers\\UserController#store');
$router->map('PUT', '/v1/users/[i:id]', 'Src\\Controllers\\UserController#update');
$router->map('DELETE', '/v1/users/[i:id]', 'Src\\Controllers\\UserController#destroy');
