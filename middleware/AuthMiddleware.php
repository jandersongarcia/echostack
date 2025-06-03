<?php

namespace Middleware;

class AuthMiddleware
{
    public function handle($request)
    {
        // Lê o cabeçalho Authorization
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        // Verifica o padrão "Bearer chave"
        if (strpos($authHeader, 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        // Extrai a chave enviada
        $apiKey = trim(str_replace('Bearer ', '', $authHeader));

        // Compara com a chave salva no .env
        if ($apiKey !== $_ENV['API_KEY']) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }
    }
}
