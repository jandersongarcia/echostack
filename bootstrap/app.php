<?php

use Dotenv\Dotenv;
use Medoo\Medoo;
use Core\MiddlewareLoader;
use App\Services\LoggerFactory;
use App\Services\TelegramNotifier;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;

// Carrega o autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Carrega configurações
$config = require __DIR__ . '/../config/config.php';

// Inicializa banco de dados
$database = new Medoo($config['db']);

// Inicializa Logger via Factory
$logger = LoggerFactory::create();

// Handler para envio ao Telegram (corrigido para Monolog 3.x)
class TelegramHandler extends AbstractProcessingHandler
{
    private TelegramNotifier $notifier;

    public function __construct(TelegramNotifier $notifier, Level $level = Level::Error, bool $bubble = true)
    {
        // Passa o valor numérico para o AbstractProcessingHandler
        parent::__construct($level->value, $bubble);
        $this->notifier = $notifier;
    }

    protected function write(LogRecord $record): void
    {
        $this->notifier->notify($record->level->getName(), $record->message);
    }
}

// Só ativa Telegram se configurado no .env
if (getenv('TELEGRAM_BOT_TOKEN') && getenv('TELEGRAM_CHAT_ID')) {
    $notifier = new TelegramNotifier();
    $telegramHandler = new TelegramHandler($notifier, Level::Critical);
    $logger->pushHandler($telegramHandler);
}

// Inicializa Middleware Loader
$middlewareLoader = new MiddlewareLoader($logger);

// Inicializa o Router
$router = new \AltoRouter();
$router->setBasePath('/v1');

// Carrega as rotas
require_once __DIR__ . '/../routes/web.php';

// Dispatcher
return new class ($router, $database, $logger, $middlewareLoader) {
    protected $router;
    protected $db;
    protected $logger;
    protected $middlewareLoader;

    public function __construct($router, $db, $logger, $middlewareLoader)
    {
        $this->router = $router;
        $this->db = $db;
        $this->logger = $logger;
        $this->middlewareLoader = $middlewareLoader;
    }

    public function run()
    {
        try {
            $match = $this->router->match();

            if ($match) {
                [$controllerClass, $method] = explode('@', $match['target']);

                $middlewares = $this->middlewareLoader->load();
                foreach ($middlewares as $middleware) {
                    $middleware->handle($_REQUEST);
                }

                $controller = new $controllerClass($this->db, $this->logger);
                $response = call_user_func_array([$controller, $method], $match['params']);

                if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
                    $response->send();
                } else {
                    echo $response;
                }
            } else {
                $this->logger->warning('Route not found', [
                    'request_uri' => $_SERVER['REQUEST_URI'],
                    'method' => $_SERVER['REQUEST_METHOD']
                ]);

                header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
                echo '404 - Page not found';
            }
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected execution error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
            echo 'Internal server error';
        }
    }
};
