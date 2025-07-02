<?php

namespace Core\Scripts;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;

// Resolve dinamicamente a raiz do projeto
$basePath = PathResolver::basePath();
echo "Base Path being used: {$basePath}\n";

// Verifica se o .env existe
$envPath = $basePath . '/.env';
if (!file_exists($envPath)) {
    echo "❌ .env file not found at expected location: {$envPath}\n";
    exit(1);
}
echo "✅ .env file found.\n";

// Carrega variáveis do .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->safeLoad();

// Verifica variáveis obrigatórias
$requiredVars = ['TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID'];
$missing = [];

foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        $missing[] = $var;
    }
}

if (!empty($missing)) {
    echo "❌ Missing required .env variables:\n";
    foreach ($missing as $var) {
        echo " - {$var}\n";
    }
    exit(2);
}

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
$chatId = $_ENV['TELEGRAM_CHAT_ID'];
$message = '🚀 Telegram test message from EchoAPI';

if (trim($message) === '') {
    echo "❌ Message text is empty. Nothing was sent to Telegram.\n";
    exit(3);
}

echo "🔧 Testing Telegram configuration...\n";
echo "🔑 Bot Token: " . substr($botToken, 0, 10) . "***********\n";
echo "💬 Chat ID: {$chatId}\n";

// Monta a requisição para a API do Telegram
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'Markdown'
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'timeout' => 10,
    ],
];

$context = stream_context_create($options);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);

if ($result === false) {
    echo "❌ cURL error: " . curl_error($ch) . "\n";
    exit(4);
}
curl_close($ch);

$response = json_decode($result, true);

if (!is_array($response)) {
    echo "❌ Invalid response from Telegram API.\nRaw: {$result}\n";
    exit(5);
}

if ($response['ok'] ?? false) {
    echo "✅ Test message sent successfully to Telegram.\n";
    exit(0);
}

// Erro conhecido
echo "❌ Telegram API returned an error:\n";

if (isset($response['description'])) {
    echo "📄 Description: {$response['description']}\n";
}

if (isset($response['error_code'])) {
    echo "🚫 Error Code: {$response['error_code']}\n";
    switch ($response['error_code']) {
        case 400:
            echo "⚠ Possible causes: invalid chat ID or malformed request.\n";
            break;
        case 401:
            echo "⚠ Invalid BOT token.\n";
            break;
        case 403:
            echo "⚠ Bot was not added to the group or lacks permission.\n";
            break;
        default:
            echo "⚠ Unknown error.\n";
    }
}

echo "\nFull API response:\n";
print_r($response);
exit(6);
