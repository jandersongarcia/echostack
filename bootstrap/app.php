<?php

use Dotenv\Dotenv;
use Medoo\Medoo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Core\MiddlewareLoader;

// Carrega variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Carrega configurações
$config = require __DIR__ . '/../config/config.php';

// Inicializa banco de dados
$database = new Medoo($config['db']);

// Inicializa Logger
$logger = new Logger('app');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));

// Inicializa Middleware Loader
$middlewareLoader = new MiddlewareLoader($logger);

// Inicializa o Router
$router = new \AltoRouter();
$router->setBasePath('/v1');

// Carrega as rotas
require_once __DIR__ . '/../routes/web.php';

// Sistema de Dispatcher com Middleware
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

                // Carrega e executa os middlewares
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
