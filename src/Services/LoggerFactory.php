<?php

namespace App\Services;

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\IntrospectionProcessor;

class LoggerFactory
{
    public static function create(): Logger
    {
        $logger = new Logger('app');

        // Diretório base absoluto para logs
        $logPath = realpath(__DIR__ . '/../../logs');

        // Handler geral: tudo vai para app.log
        $appHandler = new StreamHandler("$logPath/app.log", Level::Debug->value);
        $logger->pushHandler($appHandler);

        // Handler de erros: ERROR+ vai para errors.log
        $errorHandler = new StreamHandler("$logPath/errors.log", Level::Error->value);
        $logger->pushHandler($errorHandler);

        // Handler de segurança: WARNING até CRITICAL vai para security.log
        $securityHandler = new StreamHandler("$logPath/security.log", Level::Warning->value);
        $filteredSecurityHandler = new FilterHandler(
            $securityHandler, 
            Level::Warning->value, 
            Level::Critical->value
        );
        $logger->pushHandler($filteredSecurityHandler);

        // Processador de contexto único (gera ID para cada request)
        $logger->pushProcessor(new UidProcessor());

        // Processador opcional para debug (mostra de onde o log foi disparado)
        $logger->pushProcessor(new IntrospectionProcessor());

        // Processor customizado para IP e User-Agent
        $logger->pushProcessor(function ($record) {
            $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $record['extra']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI';
            return $record;
        });

        return $logger;
    }
}
