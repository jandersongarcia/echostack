<?php

namespace Core;

use Middleware\AuthMiddleware;

class MiddlewareLoader
{
    protected $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function load(): array
    {
        $middlewares = [];

        // Registro dos middlewares e suas dependências
        $middlewareMap = [
            AuthMiddleware::class => [$this->logger],
            // Exemplo futuro:
            // RateLimitMiddleware::class => [$this->logger, $this->cache]
        ];

        foreach ($middlewareMap as $middlewareClass => $dependencies) {
            $middlewares[] = new $middlewareClass(...$dependencies);
        }

        return $middlewares;
    }
}
