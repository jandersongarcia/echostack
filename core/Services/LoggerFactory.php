<?php

namespace Core\Services;

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Core\Helpers\PathResolver;
use Core\Services\TelegramNotifier;

class LoggerFactory
{
    public static function create(string $channel = 'app'): Logger
    {
        $logger = new Logger($channel);

        // Define timezone global dos logs
        $timezone = new \DateTimeZone($_ENV['TIME_ZONE'] ?? 'UTC');
        $logger->setTimezone($timezone);

        $logPath = PathResolver::logsPath();

        $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

        if ($botToken && $chatId) {
            $notifier = new TelegramNotifier($botToken, $chatId);
            $logger->pushProcessor(function ($record) use ($notifier) {
                $telegramLevel = \Core\Helpers\LogLevelHelper::getTelegramLogLevel();
                if ($record['level'] >= $telegramLevel->value) {
                    $formatted = self::formatTelegramMessage($record);
                    $notifier->send($formatted);
                }
                return $record;
            });
        }

        // Dias de retenção dos logs configuráveis via .env (padrão: 14)
        $envDays = $_ENV['LOG_RETENTION_DAYS'] ?? 14;
        $retentionDays = is_numeric($envDays) ? max((int) $envDays, 1) : 14;

        $logger->pushHandler(
            new RotatingFileHandler("{$logPath}/{$channel}.log", $retentionDays, Level::Info)
        );

        $logger->pushHandler(
            new RotatingFileHandler("{$logPath}/{$channel}_error.log", $retentionDays, Level::Error)
        );


        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(function ($record) {
            $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $record['extra']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
            $record['extra']['uri'] = $_SERVER['REQUEST_URI'] ?? 'CLI';
            return $record;
        });

        return $logger;
    }

    private static function formatTelegramMessage(\Monolog\LogRecord $record)
    {
        $emoji = match (true) {
            $record['level'] >= Level::Critical->value => '🚨',
            $record['level'] >= Level::Error->value => '⛔',
            $record['level'] >= Level::Warning->value => '⚠️',
            default => 'ℹ️',
        };

        $message = "{$emoji} *{$record['level_name']}* - {$record['message']}\n\n";

        if (!empty($record['context']['exception']) && $record['context']['exception'] instanceof \Throwable) {
            $e = $record['context']['exception'];
            $message .= "🧾 *Exception:* " . get_class($e) . "\n";
            $message .= "📄 *Arquivo:* {$e->getFile()}:{$e->getLine()}\n";
            $message .= "💬 *Mensagem:* {$e->getMessage()}\n\n";
        }

        $message .= "📌 *Endpoint:* " . ($record['extra']['uri'] ?? 'N/D') . "\n";
        $message .= "🌐 *IP:* " . ($record['extra']['ip'] ?? 'N/D') . "\n";
        $message .= "📱 *Agente:* " . ($record['extra']['user_agent'] ?? 'N/D') . "\n";
        $message .= "🆔 *UID:* " . ($record['extra']['uid'] ?? 'N/D') . "\n";

        return $message;
    }

    public static function createDiagnosticsLogger(?array $data = null): Logger
    {
        $logger = new Logger('diagnostics');

        $timezone = new \DateTimeZone($_ENV['TIME_ZONE'] ?? 'UTC');
        $logger->setTimezone($timezone);

        $logPath = PathResolver::logsPath();

        $envDays = $_ENV['LOG_RETENTION_DAYS'] ?? 14;
        $retentionDays = is_numeric($envDays) ? max((int) $envDays, 1) : 14;

        $logger->pushHandler(new RotatingFileHandler("$logPath/diagnostics.log", $retentionDays, Level::Debug));
        $logger->pushHandler(new \Monolog\Handler\StreamHandler("$logPath/diagnostics.log", Level::Debug, false));

        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(function ($record) {
            $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $record['extra']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
            $record['extra']['uri'] = $_SERVER['REQUEST_URI'] ?? 'CLI';
            return $record;
        });

        if ($data) {
            $logger->info('Request diagnostics', $data);

            $threshold = (int) ($_ENV['DIAGNOSTICS_THRESHOLD_MS'] ?? 150);
            if (($data['execution_time_ms'] ?? 0) > $threshold) {
                $logger->warning('⚠️ High execution time detected', $data);
            }
        }

        return $logger;
    }



}
