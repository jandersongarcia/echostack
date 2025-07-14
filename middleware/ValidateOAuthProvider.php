<?php
namespace Middleware;

class ValidateOAuthProvider
{
    public static function handle(array $params): void
    {
        $providerName = $params['provider'] ?? null;

        if (!$providerName) {
            http_response_code(400);
            echo json_encode(['error' => 'missing_provider']);
            exit;
        }

        $providers = require __DIR__ . '/../config/oauth_providers.php';

        if (!isset($providers[$providerName])) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_provider']);
            exit;
        }
    }
}
