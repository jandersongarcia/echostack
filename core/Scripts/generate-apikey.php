<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Helpers\PathResolver;

function generateKey(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

$envPath = PathResolver::envPath();
$newJwtSecret = generateKey();
$newApiKey = generateKey();

if (!file_exists($envPath)) {
    exit("❌ .env file not found. Please create it first.\n");
}

if (!is_writable($envPath)) {
    exit("❌ Permission denied: cannot modify .env file.\n");
}

$envLines = file($envPath, FILE_IGNORE_NEW_LINES);
$updatedLines = [];
$jwtExists = false;
$apiKeyExists = false;

foreach ($envLines as $line) {
    if (preg_match('/^JWT_SECRET\s*=/', $line)) {
        $updatedLines[] = "JWT_SECRET={$newJwtSecret}";
        $jwtExists = true;
    } elseif (preg_match('/^API_KEY\s*=/', $line)) {
        $updatedLines[] = "API_KEY={$newApiKey}";
        $apiKeyExists = true;
    } else {
        $updatedLines[] = $line;
    }
}

// Append if not found
if (!$jwtExists) {
    $updatedLines[] = "JWT_SECRET={$newJwtSecret}";
}

if (!$apiKeyExists) {
    $updatedLines[] = "API_KEY={$newApiKey}";
}

file_put_contents($envPath, implode(PHP_EOL, $updatedLines) . PHP_EOL);

echo "🔐 JWT_SECRET: {$newJwtSecret}\n";
echo "🔑 API_KEY:    {$newApiKey}\n";
echo "✅ .env updated successfully (values only, structure preserved).\n";
