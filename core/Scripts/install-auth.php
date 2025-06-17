<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once ('helper-script.php');

use Medoo\Medoo;

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2) . '/');
$dotenv->load();

try {
    // Database connection using Medoo
    $db = new Medoo([
        'type' => 'mysql',
        'host' => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'charset' => 'utf8mb4'
    ]);

    $pdo = $db->pdo;

    // Read SQL file
    $sqlPath = dirname(__DIR__) . '/stubs/auth/migration/auth.sql';
    if (!file_exists($sqlPath)) {
        throw new Exception("SQL file not found: " . $sqlPath);
    }

    $sql = file_get_contents($sqlPath);
    $pdo->exec($sql);

    out('SUCCESS', "Authentication tables created successfully", 'green');

} catch (Exception $e) {
    out('ERROR', "Failed to install authentication tables: " . $e->getMessage(), 'red');
    exit(1);
}
