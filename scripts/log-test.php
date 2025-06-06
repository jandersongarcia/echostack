<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Services\LoggerFactory;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Instantiate logger via factory
$logger = LoggerFactory::create();

// Generate test logs

$logger->debug("Test: debug message");
$logger->info("Test: info message");
$logger->notice("Test: notice message");
$logger->warning("Test: security warning");
$logger->error("Test: error message");
$logger->critical("Test: critical failure");

echo "✅ Logs generated. Check your /logs directory.\n";
