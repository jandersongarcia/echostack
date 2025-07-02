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

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            $this->writeToLog("❌ TelegramNotifier cURL error: {$error}");
            curl_close($ch);
            return;
        }

        $response = json_decode($result, true);
        curl_close($ch);

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
