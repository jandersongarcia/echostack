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

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$config = require __DIR__ . '/../config/config.php';

// ==========================
// BANCO DE DADOS
// ==========================

$database = new Medoo($config['db']);

// ==========================
// AUTOMATIC MIGRATION (dev)
// ==========================

$shouldMigrate = isset($_ENV['AUTO_MIGRATE']) && $_ENV['AUTO_MIGRATE'] === 'true';
$tableExists = $database->query("SHOW TABLES LIKE 'users'")->fetch();

if ($shouldMigrate && !$tableExists) {
    $migrationFile = __DIR__ . '/../core/migrations/auth-migrations.sql';

    if (file_exists($migrationFile)) {
        try {
            $sql = file_get_contents($migrationFile);
            $database->pdo->exec($sql);
            error_log("[EchoAPI] Database initialized using 'auth-migrations.sql'.");
        } catch (PDOException $e) {
            error_log("[EchoAPI] Failed to execute migration: " . $e->getMessage());
        }
    } else {
        error_log("[EchoAPI] Database initialized using 'auth-migrations.sql'.");
        echo "[EchoAPI] Database initialized using 'auth-migrations.sql'.\n";
    }
}


// ==========================
// LOGGER
// ==========================

$logger = LoggerFactory::create();  // aqui já vem com os handlers locais prontos

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
        $this->notifier->send("[" . $record->level->getName() . "] " . $record->message);
    }
}

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
$chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

if (!empty($botToken) && !empty($chatId)) {
    $notifier = new TelegramNotifier($botToken, $chatId);
    $level = LogLevelHelper::getTelegramLogLevel();
    $telegramHandler = new TelegramHandler($notifier, $level);
    
    $logger->pushHandler($telegramHandler);
}

// ==========================
// MIDDLEWARES
// ==========================

$middlewareLoader = new \Core\MiddlewareLoader($logger, $database);

// ==========================
// ROTAS
// ==========================

$router = new \AltoRouter();
$router->setBasePath('/v1');
require_once __DIR__ . '/../routes/web.php';

// ==========================
// DISPATCHER
// ==========================

return new Dispatcher($router, $database, $logger, $middlewareLoader);
