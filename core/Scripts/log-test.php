<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;
use Core\Services\LoggerFactory;

// Resolve project root path
$rootPath = PathResolver::basePath();

// Load environment variables
$dotenv = Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();  // safeLoad permite continuar mesmo se o .env não existir (opcional)

// Initialize logger via factory
$logger = LoggerFactory::create();

// Generate test logs at multiple levels
$logger->debug("Test: debug message");
$logger->info("Test: info message");
$logger->notice("Test: notice message");
$logger->warning("Test: security warning");
$logger->error("Test: error message");
$logger->critical("Test: critical failure");

echo "✅ Logs successfully generated. Check your /storage/logs directory.\n";
