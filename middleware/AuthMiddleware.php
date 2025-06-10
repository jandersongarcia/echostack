<?php

namespace Middleware;

use Monolog\Logger;

class AuthMiddleware
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function handle($request)
    {
        // Se for ambiente de desenvolvimento e for GET, libera
        if (
            ($_ENV['APP_ENV'] ?? '') === 'development' &&
            ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET'
        ) {
            return;
        }

        // Lê o cabeçalho Authorization
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        // Verifica o padrão "Bearer chave"
        if (strpos($authHeader, 'Bearer ') !== 0) {
            $this->logger->warning('AuthMiddleware: Bearer token missing or invalid format', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
                'authorization_header' => $authHeader
            ]);

            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        // Extrai a chave enviada
        $apiKey = trim(str_replace('Bearer ', '', $authHeader));

        // Compara com a chave salva no .env
        if ($apiKey !== $_ENV['API_KEY']) {
            $this->logger->error('AuthMiddleware: Invalid API key attempt', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
                'provided_key' => $apiKey
            ]);

            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
}
