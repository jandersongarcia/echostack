<?php
global $router;
global $database, $logger;

use Symfony\Component\HttpFoundation\Request;
use Core\Utils\Core\SystemInfo;

$routesPath = __DIR__;

// Rota raiz da API
$router->map('GET', '/', function() {
    echo SystemInfo::fullSignature();
});

require_once $routesPath . '/swagger.php';

// Carrega todas as rotas versionadas, exceto a V0
$versionFiles = glob($routesPath . '/V*.php');
foreach ($versionFiles as $file) {
    if (strtolower(basename($file)) !== 'v0.php') {
        require_once $file;
    }
}

/*
global $router;
global $database, $logger;
use V1\Controllers\HealthController;
use Symfony\Component\HttpFoundation\Request;
use Core\Utils\Core\SystemInfo;

$routesPath = __DIR__;

$router->map('GET', '/', function() {
    echo SystemInfo::fullSignature();
});

// Healthcheck
$router->map('GET', '/v1/health', function() use ($database, $logger) {
    $controller = new HealthController($database, $logger);
    return $controller->check();
});

require_once $routesPath . '/swagger.php';

// Carrega todas as rotas versionadas, exceto a V0
$versionFiles = glob($routesPath . '/v*.php');

foreach ($versionFiles as $file) {
    if (strtolower(basename($file)) !== 'v0.php') {
        require_once $file;
    }
}
    */
