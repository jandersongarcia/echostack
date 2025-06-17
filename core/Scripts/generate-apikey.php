<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Helpers\PathResolver;

function generateApiKey(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

$envPath = PathResolver::envPath();
$newKey = generateApiKey();

if (!file_exists($envPath)) {
    if (!is_writable(dirname($envPath))) {
        exit("❌ Permission denied: cannot write .env file in project root.\n");
    }

    file_put_contents($envPath, "SECRET_KEY={$newKey}\n");
    echo "✅ .env file created and SECRET_KEY set.\n";
} else {
    if (!is_writable($envPath)) {
        exit("❌ Permission denied: cannot modify existing .env file.\n");
    }

    $envContent = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $keyUpdated = false;

    foreach ($envContent as &$line) {
        if (strpos($line, 'SECRET_KEY=') === 0) {
            $line = "SECRET_KEY={$newKey}";
            $keyUpdated = true;
        }
    }

    if (!$keyUpdated) {
        $envContent[] = "SECRET_KEY={$newKey}";
    }

    file_put_contents($envPath, implode(PHP_EOL, $envContent) . PHP_EOL);
    echo "✅ SECRET_KEY successfully updated.\n";
}

echo "🔑 New SECRET_KEY: {$newKey}\n";
