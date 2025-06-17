<?php

namespace Core;

use Medoo\Medoo;
use Middleware\AuthMiddleware;

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
        return [
            new AuthMiddleware($this->logger, $this->db),
        ];
    }
}
