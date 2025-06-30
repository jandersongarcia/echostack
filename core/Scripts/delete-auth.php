<?php

/**
 * Script: delete-auth.php
 * Purpose: Safely removes all authentication files (Controller, Service, Middleware, SQL) and only the /auth routes from routes/web.php and public-routes.php.
 * If called with "all" flag, also drops the related database tables (users, password_resets, tokens).
 */

define('DIR', dirname(__DIR__, 2));
require_once 'helper-script.php';
require_once DIR . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Medoo\Medoo;

// Load .env
$dotenv = Dotenv::createImmutable(DIR);
$dotenv->load();

// Connect to DB
$database = new Medoo([
    'type' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4'
]);

// Check if "all" flag was passed
$deleteTables = in_array('all', $argv);
out('INFO', $deleteTables ? 'Mode: FULL deletion (files + routes + database tables)' : 'Mode: Files and routes only');

// === Delete Auth-related PHP files ===
$filesToDelete = [
    DIR . '/src/Controllers/AuthController.php',
    DIR . '/src/Services/AuthService.php',
    DIR . '/Middleware/JwtAuthMiddleware.php'
];

foreach ($filesToDelete as $file) {
    $fileLabel = str_replace(DIR, '', $file);
    if (file_exists($file)) {
        unlink($file);
        out('INFO', "File deleted: {$fileLabel}");
    } else {
        out('WARNING', "File not found: {$fileLabel}", 'yellow');
    }
}

// === Remove /auth routes from routes/web.php and clean extra blank lines ===
$routeFile = DIR . '/routes/web.php';
if (file_exists($routeFile)) {
    $lines = file($routeFile);
    $output = '';
    $blankLineFlag = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Skip auth routes and the generated comment
        if (strpos($trimmed, '/auth/') !== false || strpos($trimmed, 'Auto-generated CRUD routes for Auth') !== false) {
            out('INFO', "Removed from web.php: " . $trimmed);
            continue;
        }

        // Clean multiple consecutive blank lines
        if ($trimmed === '') {
            if (!$blankLineFlag) {
                $output .= PHP_EOL;
                $blankLineFlag = true;
            }
        } else {
            $output .= $line;
            $blankLineFlag = false;
        }
    }

    file_put_contents($routeFile, trim($output) . PHP_EOL);
    out('SUCCESS', 'Auth routes and blank lines cleaned from routes/web.php', 'green');
} else {
    out('WARNING', 'routes/web.php not found.', 'yellow');
}

// === Remove /auth public routes from public-routes.php ===
$publicRouteFile = DIR . '/routes/public-routes.php';
if (file_exists($publicRouteFile)) {
    $currentRoutes = include $publicRouteFile;
    if (!is_array($currentRoutes)) {
        $currentRoutes = [];
    }

    $filteredRoutes = array_filter($currentRoutes, function ($route) {
        return strpos($route, '/auth/') === false;
    });

    // Regrava com short array syntax ([])
    $formattedRoutes = "[\n";
    foreach ($filteredRoutes as $route) {
        $formattedRoutes .= "    '" . addslashes($route) . "',\n";
    }
    $formattedRoutes .= "];\n";

    file_put_contents($publicRouteFile, "<?php\nreturn " . $formattedRoutes);
    out('SUCCESS', 'Auth routes removed from routes/public-routes.php', 'green');
} else {
    out('WARNING', 'routes/public-routes.php not found.', 'yellow');
}

// === Optional: Drop database tables if "all" flag was passed ===
if ($deleteTables) {
    try {
        $database->pdo->exec("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS tokens;
            DROP TABLE IF EXISTS password_resets;
            DROP TABLE IF EXISTS users;
            SET FOREIGN_KEY_CHECKS=1;
        ");
        out('SUCCESS', 'Auth-related tables dropped from the database.', 'green');
    } catch (PDOException $e) {
        out('ERROR', 'Failed to drop tables: ' . $e->getMessage(), 'red');
    }
} else {
    out('INFO', 'Database tables were kept. Run \"composer delete:auth all\" if you want to drop them.', 'yellow');
}

// Rebuild Swagger docs
out('INFO', 'Running Swagger build...');
echo shell_exec("composer swagger:build");

out('SUCCESS', 'Auth deletion process completed.', 'green');
