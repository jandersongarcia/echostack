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

// Auto-generated CRUD routes for Auth
$router->map('POST', '/auth/login', 'App\Controllers\AuthController@login');
$router->map('POST', '/auth/register', 'App\Controllers\AuthController@register');
$router->map('POST', '/auth/forgot-password', 'App\Controllers\AuthController@forgotPassword');
$router->map('POST', '/auth/reset-password', 'App\Controllers\AuthController@resetPassword');
$router->map('GET', '/auth/reset-password', 'App\Controllers\AuthController@resetPassword');
$router->map('POST', '/auth/logout', 'App\Controllers\AuthController@logout');