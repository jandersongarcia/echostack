<?php

namespace Core;

use Symfony\Component\HttpFoundation\Response;
use Monolog\Logger;
use Medoo\Medoo;
use Core\MiddlewareLoader;

class Dispatcher
{
    private $router;
    private $db;
    private $logger;
    private $middlewareLoader;

    public function __construct($router, Medoo $db, Logger $logger, MiddlewareLoader $middlewareLoader)
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

            // Executar todos os middlewares antes do Controller
            $middlewares = $this->middlewareLoader->load();
            foreach ($middlewares as $middleware) {
                $middleware->handle($params);
            }

            // Executar rota via Closure
            if (is_callable($target)) {
                // Closures aceitam parâmetros individuais
                $response = call_user_func_array($target, $params);
            }
            // Executar rota via Controller@method
            elseif (is_string($target) && str_contains($target, '@')) {
                [$controllerClass, $method] = explode('@', $target);

                // Instancia Controller
                $controller = new $controllerClass($this->db, $this->logger);

                /**
                 * 🚀 ATENÇÃO:
                 * Aqui usamos call_user_func e passamos $params como ÚNICO argumento
                 * pois seus Controllers esperam:
                 *   public static function method($params)
                 */
                $response = call_user_func([$controller, $method], $params);
            }
            // Rota inválida
            else {
                $this->logger->warning('Invalid Route', [
                    'request_uri' => $_SERVER['REQUEST_URI'],
                    'method' => $_SERVER['REQUEST_METHOD']
                ]);

                http_response_code(404);
                echo json_encode(['error' => 'Invalid Route']);
                return;
            }

            // Enviar resposta se for objeto Response
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
