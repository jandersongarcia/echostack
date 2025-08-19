<?php

use Core\Routing\Router;
use V1\Controllers\HealthController;

global $database, $logger;

Router::group('/V1', function () use ($database, $logger) {
    global $router;

    $router->map('GET', '/health', function () use ($database, $logger) {
        $controller = new HealthController($database, $logger);
        return $controller->check();
    });
});
