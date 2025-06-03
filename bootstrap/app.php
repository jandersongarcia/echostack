<?php

use Dotenv\Dotenv;
use Medoo\Medoo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
new Middleware\AuthMiddleware;

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

// Inicializa o Router
$router = new \AltoRouter();
$router->setBasePath('/v1');


// Carrega as rotas
require_once __DIR__ . '/../routes/web.php';

// Sistema de Dispatcher com Middleware
return new class($router, $database, $logger) {

    protected $router;
    protected $db;
    protected $logger;

    public function __construct($router, $db, $logger) {
        $this->router = $router;
        $this->db = $db;
        $this->logger = $logger;
    }

    public function run() {
        $match = $this->router->match();

        if ($match) {
            [$controllerClass, $method] = explode('@', $match['target']);

            $middlewares = [
                new \Middleware\AuthMiddleware()
            ];

            foreach ($middlewares as $middleware) {
                $middleware->handle($_REQUEST);
            }

            $controller = new $controllerClass($this->db, $this->logger);
            call_user_func_array([$controller, $method], $match['params']);
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            echo '404 - Página não encontrada';
        }
    }
};
