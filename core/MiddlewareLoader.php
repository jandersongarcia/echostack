<?php

namespace Core;

use Medoo\Medoo;
use Middleware\AuthMiddleware;
use Core\Services\CacheService;

class MiddlewareLoader
{
    protected $logger;
    protected ?Medoo $db;

    public function __construct($logger, ?Medoo $db = null)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function load(): array
    {
        $cache = new CacheService();

        $middlewares = [];

        if ($this->db !== null) {
            $middlewares[] = new AuthMiddleware($this->logger, $this->db, $cache);
        }

        // Você pode adicionar middlewares que funcionam sem banco aqui, se desejar
        return $middlewares;
    }
}
