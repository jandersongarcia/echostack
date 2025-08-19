<?php

namespace Core;

use Symfony\Component\HttpFoundation\Response;
use Monolog\Logger;
use Medoo\Medoo;
use Core\MiddlewareLoader;
use AltoRouter;
use Core\Services\CacheService;
use Core\Routing\Router;

class Dispatcher
{
    private $router;
    private ?Medoo $db;
    private $logger;
    private $middlewareLoader;
    private $cache;

    public function __construct(AltoRouter $router, ?Medoo $db, Logger $logger, MiddlewareLoader $middlewareLoader)
    {
        $this->router = $router;
        $this->db = $db;
        $this->logger = $logger;
        $this->middlewareLoader = $middlewareLoader;
        $this->cache = new CacheService(); // ou injete se preferir

        $this->initializeRoutes();
    }

    private function initializeRoutes(): void
    {
        // Corrige prefixo /v1 para /V1 para compatibilidade
        $_SERVER['REQUEST_URI'] = preg_replace_callback(
            '#^/v(\d+)(/.*)?$#i',
            fn($m) => '/V' . $m[1] . ($m[2] ?? ''),
            $_SERVER['REQUEST_URI']
        );

        $version = null;
        if (preg_match('#^/v(\d+)(/.*)?$#i', $_SERVER['REQUEST_URI'], $matches)) {
            $version = $matches[1] ?? null;
        }

        if ($_ENV['ECHO_INSTALLED'] === 'false') {
            require_once ROOT . '/routes/v0.php';
        } else {
            $version = $version ?: '1';
            $routesFile = ROOT . "/routes/web.php";

            if (file_exists($routesFile)) {
                require_once $routesFile;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Invalid API version']);
                exit;
            }
        }
    }

    public function run()
    {
        try {
            $match = Router::getRouter()->match();

            if (!$match) {
                $this->logger->warning('Route not found', [
                    'request_uri' => $_SERVER['REQUEST_URI'],
                    'method' => $_SERVER['REQUEST_METHOD']
                ]);

                http_response_code(404);
                echo json_encode(['error' => 'Page not found']);
                return;
            }

            $target = $match['target'];
            $params = $match['params'] ?? [];

            // Middleware aliases
            $middlewareAliases = require ROOT . '/config/middleware.php';
            $routeMiddlewares = [];

            // Se for array com middlewares + target
            if (is_array($target)) {
                $raw = $target;
                $target = array_pop($raw); // último item = controller ou closure
                $routeMiddlewares = $raw;
            }

            // Resolve middlewares
            $resolvedMiddlewares = [];
            foreach ($routeMiddlewares as $alias) {
                if (isset($middlewareAliases[$alias]) && $middlewareAliases[$alias]) {
                    $resolvedMiddlewares[] = [$middlewareAliases[$alias], 'handle'];
                }
            }

            // Executa middlewares
            foreach ($resolvedMiddlewares as [$class, $method]) {
                $middleware = new $class($this->logger, $this->db, $this->cache);
                $middleware->$method($params);
            }

            // Executa o controlador
            if (is_callable($target)) {
                $response = call_user_func_array($target, $params);
            } elseif (is_string($target) && str_contains($target, '@')) {
                [$controllerClass, $method] = explode('@', $target);
                $controller = new $controllerClass($this->db, $this->logger);
                $response = call_user_func_array([$controller, $method], $params);

            } else {
                $this->logger->warning('Invalid Route', [
                    'request_uri' => $_SERVER['REQUEST_URI'],
                    'method' => $_SERVER['REQUEST_METHOD']
                ]);

                http_response_code(404);
                echo json_encode(['error' => 'Invalid Route']);
                return;
            }

            if ($response instanceof Response) {
                $response->send();
            } else {
                echo $response;
            }
        } catch (\Throwable $e) {
            $this->logger->error('Unexpected execution error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            http_response_code(500);
            echo json_encode(['error' => 'internal_error']);
        }
    }
}
