<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Check if the .env file exists
$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    header('Content-Type: application/json');
    http_response_code(500);

    echo json_encode([
        'error' => 'Environment file not found',
        'message' => 'The ".env" file is required. Please rename ".env.example" to ".env" and configure your environment variables.',
        'code' => 'E001'
    ], JSON_PRETTY_PRINT);

    exit;
}

// Safely load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Validate required database environment variables
$requiredDbVars = ['DB_NAME', 'DB_HOST', 'DB_USER'];

foreach ($requiredDbVars as $var) {
    if (empty($_ENV[$var])) {
        header('Content-Type: application/json');
        http_response_code(500);

        echo json_encode([
            'error' => 'Missing environment variable',
            'message' => "The environment variable '{$var}' is missing or empty in your .env file.",
            'code' => 'E002'
        ], JSON_PRETTY_PRINT);

        exit;
    }
}

// Database configuration
$dbConfig = [
    'database_type' => 'mysql',
    'database_name' => $_ENV['DB_NAME'],
    'server'        => $_ENV['DB_HOST'],
    'username'      => $_ENV['DB_USER'],
    'password'      => $_ENV['DB_PASS'],
    'charset'       => 'utf8',
    'port'          => $_ENV['DB_PORT'] ?? 3306,
];

// Attempt database connection with error handling
try {
    $database = new Medoo\Medoo($dbConfig);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);

    echo json_encode([
        'error' => 'Database connection failed',
        'message' => $e->getMessage(),
        'code' => 'E100',
        'category' => 'critical'
    ], JSON_PRETTY_PRINT);

    exit;
}

// Additional application configuration
$appConfig = [
    'app_env' => $_ENV['APP_ENV'] ?? 'production',
    'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
];

// Return grouped configuration
return [
    'db' => $dbConfig,
    'app' => $appConfig,
    'database' => $database,
];
