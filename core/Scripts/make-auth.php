<?php

namespace Scripts;

use Exception;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once ('helper-script.php');

try {
    $base = dirname(__DIR__, 2) . '/';

    out('INFO', "Starting authentication setup...");

    // Source and destination files to be copied automatically
    $files = [
        'core/stubs/auth/src/Models/User.php' => 'src/Models/User.php',
        'core/stubs/auth/src/Services/AuthService.php' => 'src/Services/AuthService.php',
        'core/stubs/auth/src/Controllers/AuthController.php' => 'src/Controllers/AuthController.php',
        'core/stubs/auth/src/Views/emails/recover-template.php' => 'src/Views/emails/recover-template.php'
    ];

    // Create directories and copy stub files
    foreach ($files as $source => $target) {
        $fullSource = $base . $source;
        $fullTarget = $base . $target;
        $dir = dirname($fullTarget);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (!file_exists($fullTarget)) {
            copy($fullSource, $fullTarget);
            out('SUCCESS', "File created: $target", 'green');
        } else {
            out('INFO', "File already exists and was kept: $target", 'yellow');
        }
    }

    // Automatically inject auth routes into routes/web.php
    $webRoutesPath = $base . 'routes/web.php';
    $authRoutesBlock = <<<ROUTES

// Authentication routes (auto-generated)
\$router->map('POST', '/register', 'App\\Controllers\\AuthController@register');
\$router->map('POST', '/login', 'App\\Controllers\\AuthController@login');
\$router->map('POST', '/recover', 'App\\Controllers\\AuthController@recover');

ROUTES;

    if (file_exists($webRoutesPath)) {
        $webRoutesContent = file_get_contents($webRoutesPath);
        if (strpos($webRoutesContent, "AuthController@register") === false) {
            file_put_contents($webRoutesPath, PHP_EOL . $authRoutesBlock, FILE_APPEND);
            out('SUCCESS', "Authentication routes added to routes/web.php", 'green');
        } else {
            out('INFO', "Routes already exist in routes/web.php", 'yellow');
        }
    } else {
        out('ERROR', "routes/web.php not found", 'red');
    }

    // Path to the public routes file
    $publicRoutesPath = $base . 'routes/public-routes.php';

    // Minimum public routes that must be present
    $authRoutes = [
        '/login',
        '/register',
        '/recover',
        '/health'
    ];

    if (file_exists($publicRoutesPath)) {
        $existing = include $publicRoutesPath;
        $filtered = array_filter($existing, fn($r) => $r !== 'all-routes');
        $merged = array_unique(array_merge($filtered, $authRoutes));
    } else {
        $merged = $authRoutes;
    }

    // Format and write content
    $export = "<?php\nreturn [\n";
    foreach ($merged as $route) {
        $export .= "    '" . $route . "',\n";
    }
    $export .= "];\n";

    file_put_contents($publicRoutesPath, $export);
    out('SUCCESS', "routes/public-routes.php updated with authentication routes", 'green');
    echo shell_exec("composer migration:auth");
    out('CHECK', "Configure email variables in your .env file");
    out('CHECK', "Ensure AuthMiddleware is active in MiddlewareLoader and Dispatcher");

} catch (Exception $e) {
    out('ERROR', "Failed to generate authentication: " . $e->getMessage(), 'red');
    exit(1);
}
