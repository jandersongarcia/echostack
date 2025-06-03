<?php

// Carrega o autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega o Middleware
//require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use Dotenv\Dotenv;

// Carrega as variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configurações do banco de dados (puxando do .env)
$dbConfig = [
    'database_type' => 'mysql',
    'database_name' => $_ENV['DB_NAME'],
    'server'        => $_ENV['DB_HOST'],
    'username'      => $_ENV['DB_USER'],
    'password'      => $_ENV['DB_PASS'],
    'charset'       => 'utf8',
    'port'          => $_ENV['DB_PORT'] ?? 3306,
];

// Outras configurações (exemplo)
$appConfig = [
    'app_env' => $_ENV['APP_ENV'] ?? 'production',
    'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
];

// Podemos retornar as configs juntas, se quiser centralizar
return [
    'db' => $dbConfig,
    'app' => $appConfig,
];
