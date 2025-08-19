<?php

use Core\Services\LoggerFactory;
use Dotenv\Dotenv;
use Core\Services\DatabaseFactory;
use Core\Services\TelegramNotifier;
use Core\Helpers\LogLevelHelper;
use Core\Helpers\PathResolver;
use Core\MiddlewareLoader;
use Core\Dispatcher;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;
use Utils\ResponseHelper;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


define('ROOT', PathResolver::basePath());

require_once ROOT . '/vendor/autoload.php';


ob_start();

/**
 * Sends a JSON error response and exits execution
 */
function jsonErrorResponse(array $data): never
{
    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// ==========================
// LOAD CONFIGURATION (.env, DB, Logger)
// ==========================

$config = require ROOT . '/core/Bootstrap/init.php';

// ==========================
// LOAD CUSTOM HOOKS (if any)
// ==========================

$hooksPath = ROOT . '/Bootstrap/hooks.php';
if (file_exists($hooksPath)) {
    require_once $hooksPath;
}

// ==========================
// GLOBAL ATTRIBUTES
// ==========================

$logger = $config['logger'] ?? LoggerFactory::create();
$database = $config['database'] ?? null;

try {

    \Core\Utils\System\Diagnostics::start();

    // ==========================
    // TELEGRAM LOGGING HANDLER (optional)
    // ==========================

    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
    $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

    if (!empty($botToken) && !empty($chatId)) {
        $notifier = new TelegramNotifier($botToken, $chatId);
        $level = LogLevelHelper::getTelegramLogLevel();

        $telegramHandler = new class ($notifier, $level) extends AbstractProcessingHandler {
            private TelegramNotifier $notifier;

            public function __construct(TelegramNotifier $notifier, Level $level)
            {
                parent::__construct($level->value, true);
                $this->notifier = $notifier;
            }

            protected function write(LogRecord $record): void
            {
                $this->notifier->send("[" . $record->level->getName() . "] " . $record->message);
            }
        };

        $logger->pushHandler($telegramHandler);
    }

    // ==========================
    // LOAD MIDDLEWARES
    // ==========================
    global $database, $logger;
    $middlewareLoader = new MiddlewareLoader($logger, $database);

    // ==========================
    // NORMALIZE VERSION PREFIX TO UPPERCASE (/v1 → /V1)
    // ==========================
    $_SERVER['REQUEST_URI'] = preg_replace_callback(
        '#^/v(\d+)(/|$)#i',
        fn($m) => '/V' . $m[1] . $m[2],
        $_SERVER['REQUEST_URI']
    );

    // ==========================
    // REGISTER ROUTES
    // ==========================

    global $router;
    $router = new AltoRouter();
    require_once ROOT . '/routes/web.php';

    // ==========================
    // RETURN DISPATCHER INSTANCE
    // ==========================
    
    $diagnostics = \Core\Utils\System\Diagnostics::end();

    // Log diagnostics only if enabled via .env
    $diagnosticLogger = LoggerFactory::createDiagnosticsLogger($diagnostics);

    return new Dispatcher($router, $database, $logger, $middlewareLoader);

} catch (Throwable $e) {
    $logger->critical('Application bootstrap failed', ['exception' => $e]);

    ResponseHelper::jsonErrorResponse([
        'error' => 'Bootstrap failure',
        'message' => $e->getMessage(),
        'code' => 'E998',
        'category' => 'critical'
    ]);
}

