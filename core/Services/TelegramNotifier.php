<?php

namespace Core\Services;

class TelegramNotifier
{
    private $botToken;
    private $chatId;
    private $allowedLevels;

    public function __construct()
    {
        $this->botToken = getenv('TELEGRAM_BOT_TOKEN');
        $this->chatId = getenv('TELEGRAM_CHAT_ID');
        $this->allowedLevels = array_map('strtolower', explode(',', getenv('ERROR_NOTIFY_CATEGORIES') ?? ''));
    }

    public function notify(string $level, string $message): void
    {
        if (!in_array(strtolower($level), $this->allowedLevels)) {
            return;
        }

        $text = sprintf(
            "*%s ERROR*\n\n%s",
            strtoupper($level),
            $message
        );

        $this->sendTelegramMessage($text);
    }

    private function sendTelegramMessage(string $text): void
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        $data = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 5,
            ],
        ];
        $context = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }
}
