<?php

// ==========================
// AUTOLOAD E DEPENDÊNCIAS
// ==========================

use Dotenv\Dotenv;
use Medoo\Medoo;
use Core\MiddlewareLoader;
use Core\Services\LoggerFactory;
use Core\Services\TelegramNotifier;
use Core\Helpers\LogLevelHelper;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;
use Core\Dispatcher;

// Carrega o autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// ==========================
// AMBIENTE E CONFIGURAÇÃO
// ==========================

// Carrega variáveis de ambiente antes de tudo
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();  // safeLoad evita exceção caso o .env falhe

// Carrega arquivo de configuração
$config = require __DIR__ . '/../config/config.php';

// ==========================
// BANCO DE DADOS
// ==========================

$database = new Medoo($config['db']);

// ==========================
// LOGGER
// ==========================

$logger = LoggerFactory::create();

// ==========================
// TELEGRAM HANDLER (opcional)
// ==========================

class TelegramHandler extends AbstractProcessingHandler
{
    private TelegramNotifier $notifier;

    public function __construct(TelegramNotifier $notifier, Level $level = Level::Error, bool $bubble = true)
    {
        parent::__construct($level->value, $bubble);
        $this->notifier = $notifier;
    }

    protected function write(LogRecord $record): void
    {
        $this->notifier->notify($record->level->getName(), $record->message);
    }
}

// Lê as variáveis diretamente do $_ENV (mais seguro e portável)
if (!empty($_ENV['TELEGRAM_BOT_TOKEN']) && !empty($_ENV['TELEGRAM_CHAT_ID'])) {
    $notifier = new TelegramNotifier();
    $level = LogLevelHelper::getTelegramLogLevel();
    $telegramHandler = new TelegramHandler($notifier, $level);
    // Primeiro o Telegram (externo, alta prioridade)
    $logger->pushHandler($telegramHandler);

    // Depois os handlers locais
    $logger->pushHandler($appHandler);
    $logger->pushHandler($filteredSecurityHandler);
    $logger->pushHandler($errorHandler);
}

// ==========================
// MIDDLEWARES
// ==========================

$middlewareLoader = new MiddlewareLoader($logger);

// ==========================
// ROTAS
// ==========================

$router = new \AltoRouter();
$router->setBasePath('/v1');

// Carrega as rotas
require_once __DIR__ . '/../routes/web.php';

// ==========================
// RETORNA A INSTÂNCIA DA APLICAÇÃO
// ==========================

return new Dispatcher($router, $database, $logger, $middlewareLoader);
