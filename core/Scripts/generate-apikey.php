<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Helpers\PathResolver;

function generateApiKey(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}

$envPath = PathResolver::envPath();
$newJwtSecret = generateApiKey();

if (!file_exists($envPath)) {
    if (!is_writable(dirname($envPath))) {
        exit("❌ Permission denied: cannot write .env file in project root.\n");
    }

    $content = "JWT_SECRET={$newJwtSecret}\n";
    file_put_contents($envPath, $content);
    echo "✅ .env file created with JWT_SECRET.\n";
} else {
    if (!is_writable($envPath)) {
        exit("❌ Permission denied: cannot modify existing .env file.\n");
    }

    $envContent = file($envPath, FILE_IGNORE_NEW_LINES);

    $jwtUpdated = false;
    $newContent = [];

    foreach ($envContent as $line) {
        // Clean up duplicate JWT_SECRET lines if they exist
        if (strpos($line, 'JWT_SECRET=') === 0) {
            if (!$jwtUpdated) {
                $newContent[] = "JWT_SECRET={$newJwtSecret}";
                $jwtUpdated = true;
            }
            // If it's an extra JWT_SECRET, skip it
            continue;
        }

        $newContent[] = $line;
    }

    if (!$jwtUpdated) {
        // Ensure a blank line before appending if the last line is not empty
        if (count($newContent) > 0 && trim(end($newContent)) !== '') {
            $newContent[] = '';
        }
        $newContent[] = "JWT_SECRET={$newJwtSecret}";
    }

    // Remove multiple consecutive blank lines (keep max one)
    $cleanedContent = [];
    $previousEmpty = false;

    foreach ($newContent as $line) {
        if (trim($line) === '') {
            if (!$previousEmpty) {
                $cleanedContent[] = '';
                $previousEmpty = true;
            }
        } else {
            $cleanedContent[] = $line;
            $previousEmpty = false;
        }
    }

    file_put_contents($envPath, implode(PHP_EOL, $cleanedContent) . PHP_EOL);
}

echo "🔒 New JWT_SECRET: {$newJwtSecret}\n";
