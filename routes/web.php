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

require_once __DIR__ . '/swagger.php';

// OAuth routes
$router->map('GET', '/oauth/[a:provider]/redirect', 'App\\Controllers\\OAuthController@redirect');
$router->map('GET', '/oauth/[a:provider]/callback', 'App\\Controllers\\OAuthController@callback');
