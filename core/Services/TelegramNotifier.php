<?php

namespace Core\Services;

class TelegramNotifier
{
    private string $botToken;
    private string $chatId;
    private string $logPath;

    public function __construct(string $botToken, string $chatId, ?string $logPath = null)
    {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
        $this->logPath = $logPath ?? dirname(__DIR__, 2) . '/storage/logs/errors.log';
    }

    public function send(string $message): void
    {
        $text = "*ERROR ALERT*\n\n" . $message;

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $data = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];

        $options = [
            'http' => [
                'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'        => 'POST',
                'content'       => http_build_query($data),
                'timeout'       => 5,
                'ignore_errors' => true, // permite capturar erro de resposta HTTP
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            $this->writeToLog("❌ TelegramNotifier failed: {$error['message']} in {$error['file']} on line {$error['line']}");
            return;
        }

        $response = json_decode($result, true);
        if (!($response['ok'] ?? false)) {
            $description = $response['description'] ?? 'Unknown error';
            $this->writeToLog("❌ Telegram API Error: {$description}");
        }
    }

    private function writeToLog(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $line = "[{$timestamp}] {$message}" . PHP_EOL;

        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($this->logPath, $line, FILE_APPEND);
    }
}
