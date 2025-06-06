<?php

// Autoload centralizado via caminho absoluto
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;

// Resolve dinamicamente a raiz do projeto
$basePath = PathResolver::basePath();

echo "Base Path being used: " . $basePath . PHP_EOL;

// Verifica se o .env existe no local esperado
if (!file_exists($basePath . '/.env')) {
    echo "❌ .env file not found at expected location: " . $basePath . '/.env' . PHP_EOL;
    exit(1);
}

echo "✅ .env file found.\n";

// Carrega o .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->safeLoad();

// Teste se as variáveis foram carregadas corretamente
$dotenvVars = ['TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID'];
$missingVars = [];

foreach ($dotenvVars as $var) {
    if (!array_key_exists($var, $_ENV)) {
        $missingVars[] = $var;
    }
}

if (!empty($missingVars)) {
    echo "❌ The following environment variables were not loaded correctly from .env:\n";
    foreach ($missingVars as $var) {
        echo " - {$var}\n";
    }
    exit(1);
}

echo "✅ All required .env variables loaded successfully.\n";

// ⚠ ATENÇÃO: mudamos para $_ENV para evitar o problema do getenv() no Windows.
$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
$chatId = $_ENV['TELEGRAM_CHAT_ID'];
$message = '🚀 Telegram test message from EchoAPI';

// Exibe as variáveis carregadas
echo "🔧 Testing Telegram configuration...\n";
echo "🔑 Bot Token: " . substr($botToken, 0, 10) . "***********\n";
echo "💬 Chat ID: " . $chatId . "\n";

// Monta a URL da API
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'Markdown'
];

// Executa a requisição HTTP
$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'timeout' => 10,
    ],
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

// Se falhar na comunicação:
if ($result === false) {
    echo "❌ Failed to contact Telegram API. Check your internet connection or firewall.\n";
    exit(2);
}

// Decodifica a resposta da API
$response = json_decode($result, true);

// Analisa a resposta
if (isset($response['ok']) && $response['ok'] === true) {
    echo "✅ Test message sent successfully to Telegram.\n";
} else {
    echo "❌ Telegram API returned an error:\n";

    if (isset($response['description'])) {
        echo "📄 Description: " . $response['description'] . "\n";
    }

    if (isset($response['error_code'])) {
        echo "🚫 Error Code: " . $response['error_code'] . "\n";

        switch ($response['error_code']) {
            case 400:
                echo "⚠ Possible causes: invalid chat ID or malformed request.\n";
                break;
            case 403:
                echo "⚠ Possible causes: bot was not added to the group or lacks permission.\n";
                break;
            case 401:
                echo "⚠ Invalid BOT token.\n";
                break;
            default:
                echo "⚠ Unknown error.\n";
                break;
        }
    }

    echo "\nFull API response:\n";
    print_r($response);
}
