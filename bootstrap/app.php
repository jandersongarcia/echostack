<?php

// ==========================
// AUTOLOAD E DEPENDÊNCIAS
// ==========================

use Dotenv\Dotenv;
use Medoo\Medoo;
use Core\MiddlewareLoader;
use App\Services\LoggerFactory;
use App\Services\TelegramNotifier;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;
use Core\Dispatcher; // Já assumindo que você criou o Dispatcher separado

// Carrega o autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// ==========================
// AMBIENTE E CONFIGURAÇÃO
// ==========================

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

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

// Se variáveis de ambiente do Telegram existirem, adiciona o handler
if (getenv('TELEGRAM_BOT_TOKEN') && getenv('TELEGRAM_CHAT_ID')) {
    $notifier = new TelegramNotifier();
    $telegramHandler = new TelegramHandler($notifier, Level::Critical);
    $logger->pushHandler($telegramHandler);
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
