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

// Authentication routes (auto-generated)
$router->map('POST', '/register', 'App\Controllers\AuthController@register');
$router->map('POST', '/login', 'App\Controllers\AuthController@login');
$router->map('POST', '/recover', 'App\Controllers\AuthController@recover');

// Rotas automáticas para o CRUD de todo
$router->map('GET', '/todo', 'App\Controllers\TodoController@index');
$router->map('GET', '/todo/[i:id]', 'App\Controllers\TodoController@show');
$router->map('POST', '/todo', 'App\Controllers\TodoController@store');
$router->map('PUT', '/todo/[i:id]', 'App\Controllers\TodoController@update');
$router->map('DELETE', '/todo/[i:id]', 'App\Controllers\TodoController@destroy');
