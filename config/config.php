<?php

// Carrega o autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Validação das variáveis obrigatórias de banco de dados
$requiredDbVars = ['DB_NAME', 'DB_HOST', 'DB_USER'];

foreach ($requiredDbVars as $var) {
    if (empty($_ENV[$var])) {
        throw new Exception("The environment variable '{$var}' is not set or is empty.");
    }
}

// Configurações do banco de dados
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

// Retorna as configurações agrupadas
return [
    'db' => $dbConfig,
    'app' => $appConfig,
];
