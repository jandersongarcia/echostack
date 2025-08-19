<?php

/**
 * Script: make-auth.php
 * Uso: composer make:auth v1
 */

$vendorAutoload = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$vendorAutoload || !file_exists($vendorAutoload)) {
    echo "❌ vendor/autoload.php não encontrado. Execute 'composer install'.\n";
    exit(1);
}
require_once $vendorAutoload;

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;
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
        $__['make:auth'][$key] ?? $key
    );

// 1. Captura da versão
$version = $argv[1] ?? null;
if (!$version || !preg_match('/^v[0-9]+$/', $version)) {
    echo $t('usage') . "\n";
    exit(1);
}

$srcPath = "{$basePath}/app/{$version}";
$routeFile = "{$basePath}/routes/{$version}.php";

if (!is_dir($srcPath)) {
    echo $t('version_missing', ['version' => $version, 'num' => substr($version, 1)]) . "\n";
    exit(1);
}

// 2. Carrega .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->load();

$envPath = "{$basePath}/.env";
$envContent = file_get_contents($envPath);

// Se não existe ou está vazio
if (!preg_match('/^JWT_SECRET=\\S+/m', $envContent)) {
    echo $t('jwt_missing') . "\n";
    shell_exec("composer generate:key");
} else {
    echo $t('jwt_exists') . "\n";
}


// 3. Verifica driver do banco
$driver = $_ENV['DB_DRIVER'] ?? 'none';
if (!in_array($driver, ['mysql', 'pgsql'])) {
    echo $t('db_invalid') . "\n";
    exit(1);
}

// 4. Caminhos
$controllerPath = "{$srcPath}/Controllers/AuthController.php";
$servicePath = "{$srcPath}/Services/AuthService.php";
$emailDir = "{$srcPath}/Views/emails";
$emailPath = "{$emailDir}/recover-template.php";

@mkdir(dirname($controllerPath), 0775, true);
@mkdir(dirname($servicePath), 0775, true);
@mkdir($emailDir, 0775, true);

// 5. Gerar arquivos principais
if (!file_exists($controllerPath)) {
    echo $t('creating_auth_controller', ['version' => $version]) . "\n";
    file_put_contents($controllerPath, "<?php\n// AuthController para {$version}\n");
}

if (!file_exists($servicePath)) {
    echo $t('creating_auth_service', ['version' => $version]) . "\n";
    file_put_contents($servicePath, "<?php\n// AuthService para {$version}\n");
}

if (!file_exists($emailPath)) {
    echo $t('creating_email_template') . "\n";
    file_put_contents($emailPath, "<!-- Recover email template for {$version} -->");
}

// 6. Adicionar rotas
$authRoutes = [
    "\$router->map('POST', '/login', 'App\\\\{$version}\\\\Controllers\\\\AuthController@login');",
    "\$router->map('POST', '/register', 'App\\\\{$version}\\\\Controllers\\\\AuthController@register');",
    "\$router->map('POST', '/forgot-password', 'App\\\\{$version}\\\\Controllers\\\\AuthController@forgotPassword');",
    "\$router->map('POST', '/reset-password', 'App\\\\{$version}\\\\Controllers\\\\AuthController@resetPassword');",
    "\$router->map('GET', '/reset-password', 'App\\\\{$version}\\\\Controllers\\\\AuthController@resetPassword');",
    "\$router->map('POST', '/logout', 'App\\\\{$version}\\\\Controllers\\\\AuthController@logout');",
];

if (file_exists($routeFile)) {
    $existing = file_get_contents($routeFile);
    $toAdd = [];
    foreach ($authRoutes as $route) {
        if (!str_contains($existing, $route)) $toAdd[] = $route;
    }
    if (!empty($toAdd)) {
        file_put_contents($routeFile, PHP_EOL . implode(PHP_EOL, $toAdd) . PHP_EOL, FILE_APPEND);
        echo $t('routes_added', ['file' => basename($routeFile)]) . "\n";
    } else {
        echo $t('routes_skipped') . "\n";
    }
} else {
    file_put_contents($routeFile, "<?php\n" . implode(PHP_EOL, $authRoutes) . PHP_EOL);
    echo $t('routes_created') . "\n";
}

// 7. Rodar SQL de migração
$driverFile = $driver === 'mysql'
    ? "{$basePath}/core/Migration/data/auth-migrations-mysql.sql"
    : "{$basePath}/core/Migration/data/auth-migrations-postgresql.sql";

if (!file_exists($driverFile)) {
    echo $t('migration_not_found', ['version' => $version]) . "\n";
} else {
    try {
        $pdo = new PDO(
            "{$driver}:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
            $_ENV['DB_USER'], $_ENV['DB_PASS'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $sql = file_get_contents($driverFile);
        $pdo->exec($sql);
        echo $t('migration_success') . "\n";
    } catch (Throwable $e) {
        echo "\033[31m" . $t('migration_fail', ['error' => $e->getMessage()]) . "\033[0m\n";
    }
}

echo $t('finalized', ['version' => $version]) . "\n";
