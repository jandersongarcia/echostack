<?php

namespace Core\Services;

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Core\Helpers\PathResolver;
use Core\Services\TelegramNotifier;

class LoggerFactory
{
    public static function create(): Logger
    {
        $logger = new Logger('app');

        // Diretório absoluto via PathResolver
        $logPath = PathResolver::logsPath();

        // Telegram Notifier
        $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

        if ($botToken && $chatId) {
            $notifier = new TelegramNotifier($botToken, $chatId);
            $logger->pushProcessor(function ($record) use ($notifier) {
                if ($record['level'] >= Level::Critical->value) {
                    $notifier->send($record['message']);
                }
                return $record;
            });

        }

        // Handler geral: tudo vai para app.log
        $appHandler = new StreamHandler("$logPath/app.log", Level::Debug);
        $logger->pushHandler($appHandler);

        // Handler de segurança: WARNING até CRITICAL vai para security.log
        $securityHandler = new StreamHandler("$logPath/security.log", Level::Warning);
        $filteredSecurityHandler = new FilterHandler($securityHandler, Level::Warning, Level::Critical);
        $logger->pushHandler($filteredSecurityHandler);

        // Handler de erros: ERROR+ vai para errors.log
        $errorHandler = new StreamHandler("$logPath/errors.log", Level::Error);
        $logger->pushHandler($errorHandler);

        // Processadores adicionais
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(function ($record) {
            $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $record['extra']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
            return $record;
        });

        return $logger;
    }
}
