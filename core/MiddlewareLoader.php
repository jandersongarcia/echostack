<?php

namespace Core;

use Medoo\Medoo;
use Middleware\AuthMiddleware;
use Core\Services\CacheService; // não esqueça esse use

class MiddlewareLoader
{
    protected $logger;
    protected $db;

    public function __construct($logger, Medoo $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function load(): array
    {
        // Cria a instância do cache
        $cache = new CacheService();

        return [
            new AuthMiddleware($this->logger, $this->db, $cache),
        ];
    }
}
