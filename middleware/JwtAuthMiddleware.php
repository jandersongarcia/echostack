<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\JsonResponse;

class JwtAuthMiddleware
{
    public static function handle() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        if (!str_starts_with($authHeader, 'Bearer ')) {
            (new JsonResponse(['error' => 'Token ausente'], 401))->send();
            exit;
        }
        $token = trim(str_replace('Bearer ', '', $authHeader));
        try {
            $key = $jwtSecret = $_ENV['JWT_SECRET'] ?? null;
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $_SERVER['user_id'] = $decoded->sub;
        } catch (\Exception $e) {
            (new JsonResponse(['error' => 'Token inválido'], 401))->send();
            exit;
        }
    }
}