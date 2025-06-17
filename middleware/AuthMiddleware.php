<?php

namespace Middleware;

use Monolog\Logger;
use Medoo\Medoo;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthMiddleware
{
    private Logger $logger;
    private Medoo $db;

    public function __construct(Logger $logger, Medoo $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function handle($request)
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = preg_replace('#^/v1#', '', $uri);
        $publicRoutes = require __DIR__ . '/../routes/public-routes.php';

        // Libera tudo se estiver marcado como 'all-routes' no modo dev
        if (in_array('all-routes', $publicRoutes) || in_array($uri, $publicRoutes)) {
            return;
        }

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (strpos($authHeader, 'Bearer ') !== 0) {
            $this->logAndReject($authHeader, 'Invalid or expired token');
        }

        $token = trim(str_replace('Bearer ', '', $authHeader));

        $usuario = $this->db->get('tokens (t)', [
            "[>]usuarios (u)" => ["t.usuario_id" => "id"]
        ], "u.*", ["t.token" => $token]);

        if (!$usuario) {
            $this->logAndReject($authHeader, 'Token inválido ou expirado');
        }

        // Define usuário autenticado em contexto global (exemplo)
        $_SERVER['auth_user'] = $usuario;
    }

    private function logAndReject(string $token, string $motivo)
    {
        $this->logger->warning("AuthMiddleware: $motivo", [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            'authorization_header' => $token
        ]);

        $response = new JsonResponse(['error' => $motivo], 401);
        $response->send();
        exit;
    }

}
