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

// Rotas automáticas para o CRUD de todo


// Rotas automáticas para o CRUD de todo
$router->map('GET', '/todo', 'App\\Controllers\\TodoController@index');
$router->map('GET', '/todo/[i:id]', 'App\\Controllers\\TodoController@show');
$router->map('POST', '/todo', 'App\\Controllers\\TodoController@store');
$router->map('PUT', '/todo/[i:id]', 'App\\Controllers\\TodoController@update');
$router->map('DELETE', '/todo/[i:id]', 'App\\Controllers\\TodoController@destroy');
