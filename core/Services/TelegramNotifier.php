<?php

namespace Core\Services;

class TelegramNotifier
{
    private $botToken;
    private $chatId;

    public function __construct(string $botToken, string $chatId)
    {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
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
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 5,
            ],
        ];
        $context  = stream_context_create($options);
        @file_get_contents($url, false, $context);
    }
}
