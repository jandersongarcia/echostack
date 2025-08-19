<?php

/**
 * Script: delete-auth.php
 * Uso: composer delete:auth v1 [all]
 */

$vendorAutoload = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$vendorAutoload || !file_exists($vendorAutoload)) {
    echo "❌ vendor/autoload.php não encontrado. Execute 'composer install'.\n";
    exit(1);
}
require_once $vendorAutoload;

use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;
use Dotenv\Dotenv;
use Medoo\Medoo;

$basePath = PathResolver::basePath();

// Carrega idioma
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "{$basePath}/core/Lang/{$lang}.php";
if (!file_exists($langFile)) {
    $lang = 'en';
    $langFile = "{$basePath}/core/Lang/en.php";
}
$__ = include $langFile;
$t = fn($key, $replacements = []) =>
    str_replace(
        array_map(fn($k) => ":{$k}", array_keys($replacements)),
        array_values($replacements),
        $__['delete:auth'][$key] ?? $key
    );

// Captura versão
$version = $argv[1] ?? null;
$deleteTables = in_array('all', $argv);

if (!$version || !preg_match('/^v[0-9]+$/', $version)) {
    echo $t('usage') . "\n";
    exit(1);
}

$src = "{$basePath}/app/{$version}";
$routeFile = "{$basePath}/routes/{$version}.php";

if (!is_dir($src)) {
    echo $t('version_missing', ['version' => $version]) . "\n";
    exit(1);
}

echo $t('mode', ['mode' => $deleteTables ? 'FULL' : 'SAFE', 'extras' => $deleteTables ? '+ banco de dados' : '']) . "\n";

// Carrega .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->load();

$database = new Medoo([
    'type' => $_ENV['DB_DRIVER'],
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4'
]);

// Arquivos
$files = [
    "{$src}/Controllers/AuthController.php",
    "{$src}/Services/AuthService.php",
    "{$src}/Views/emails/recover-template.php"
];

foreach ($files as $file) {
    $label = str_replace($basePath, '', $file);
    if (file_exists($file)) {
        unlink($file);
        echo $t('file_deleted', ['file' => $label]) . "\n";
    } else {
        echo $t('file_not_found', ['file' => $label]) . "\n";
    }
}

// Remover rotas da versão
if (file_exists($routeFile)) {
    $lines = file($routeFile);
    $output = '';
    $blank = false;

    foreach ($lines as $line) {
        $trim = trim($line);
        if (str_contains($trim, '/login') || str_contains($trim, '/register') || str_contains($trim, '/forgot-password') || str_contains($trim, '/reset-password') || str_contains($trim, '/logout')) {
            echo $t('route_removed', ['route' => $trim]) . "\n";
            continue;
        }

        if ($trim === '') {
            if (!$blank) {
                $output .= PHP_EOL;
                $blank = true;
            }
        } else {
            $output .= $line;
            $blank = false;
        }
    }

    file_put_contents($routeFile, trim($output) . PHP_EOL);
    echo $t('routes_cleaned') . "\n";
} else {
    echo $t('webphp_missing') . "\n";
}

// Remover public-routes
$publicRouteFile = "{$basePath}/routes/public-routes.php";
if (file_exists($publicRouteFile)) {
    $routes = include $publicRouteFile;
    if (!is_array($routes)) $routes = [];

    $cleaned = array_filter($routes, fn($r) => !str_contains($r, '/auth/') && !str_contains($r, '/login') && !str_contains($r, '/register'));
    $formatted = "[\n";
    foreach ($cleaned as $r) {
        $formatted .= "    '" . addslashes($r) . "',\n";
    }
    $formatted .= "];\n";

    file_put_contents($publicRouteFile, "<?php\nreturn " . $formatted);
    echo $t('public_routes_cleaned') . "\n";
} else {
    echo $t('publicphp_missing') . "\n";
}

// Drop tables
if ($deleteTables) {
    try {
        $database->pdo->exec("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS tokens;
            DROP TABLE IF EXISTS password_resets;
            DROP TABLE IF EXISTS users;
            SET FOREIGN_KEY_CHECKS=1;
        ");
        echo $t('tables_dropped') . "\n";
    } catch (Throwable $e) {
        echo "\033[31m" . $t('tables_failed', ['error' => $e->getMessage()]) . "\033[0m\n";
    }
} else {
    echo $t('tables_skipped') . "\n";
}

// Swagger
echo $t('swagger_building') . "\n";
shell_exec("composer swagger:build");

echo $t('finalized', ['version' => $version]) . "\n";
