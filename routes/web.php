<?php

use App\Controllers\HealthController;
use Symfony\Component\HttpFoundation\Request;

// Healthcheck
$router->map('GET', '/health', function() use ($database, $logger) {
    $controller = new HealthController($database, $logger);
    return $controller->check();
});


