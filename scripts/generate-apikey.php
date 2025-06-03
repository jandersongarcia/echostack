<?php

function generateApiKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

$envPath = __DIR__ . '/../.env';
$newKey = generateApiKey();

if (!file_exists($envPath)) {
    // Cria o arquivo inteiro com a nova chave
    file_put_contents($envPath, "API_KEY={$newKey}\n");
    echo "Arquivo .env criado e API_KEY definida.\n";
} else {
    $envContent = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $keyUpdated = false;

    foreach ($envContent as &$line) {
        if (strpos($line, 'API_KEY=') === 0) {
            $line = "API_KEY={$newKey}";
            $keyUpdated = true;
        }
    }

    if (!$keyUpdated) {
        $envContent[] = "API_KEY={$newKey}";
    }

    file_put_contents($envPath, implode(PHP_EOL, $envContent) . PHP_EOL);
    echo "API_KEY atualizada com sucesso.\n";
}

echo "Nova API_KEY: {$newKey}\n";
