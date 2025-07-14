<?php

namespace App\Services;

use Medoo\Medoo;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Monolog\Logger;
use Core\Services\LoggerFactory;
use Core\Helpers\MiddlewareHelper;
use Core\Services\CacheService;

class OAuthService
{
    private Medoo $db;
    private Logger $logger;
    private string $key;

    private $cache;
    private \DateTimeZone $timezone;

    public function __construct()
    {
        $this->db = new Medoo([
            'type' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'database' => $_ENV['DB_NAME'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'charset' => 'utf8mb4'
        ]);

        $this->logger = LoggerFactory::create();
        $this->key = $_ENV['JWT_SECRET'] ?? '';
        $this->cache = new CacheService();
        $this->timezone = new \DateTimeZone($_ENV['TIME_ZONE'] ?? 'UTC');

        if (!$this->key) {
            throw new \RuntimeException('JWT_SECRET not set.');
        }
    }

    public function getProvider(string $provider)
    {
        $providers = require __DIR__ . '/../../config/oauth_providers.php';

        if (!isset($providers[$provider])) {
            throw new \InvalidArgumentException("Unknown provider: {$provider}");
        }

        $config = $providers[$provider];
        $providerClass = $config['class'];

        return new $providerClass([
            'clientId' => $config['env']['clientId'],
            'clientSecret' => $config['env']['clientSecret'],
            'redirectUri' => $config['env']['redirectUri'],
            'tenant' => $config['env']['tenant'],
        ]);
    }

    public function loginWithOAuth(string $email): array
    {
        try {
            $dateNow = (new \DateTime('now', $this->timezone))->format('Y-m-d H:i:s');

            $user = $this->db->get('users', '*', ['email' => $email]);

            if (!$user) {
                $this->logger->warning("OAuth login failed - user not found", [
                    'email_attempted' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'timestamp' => $dateNow,
                ]);
                return ['status' => 404, 'body' => ['error' => 'user_not_found']];
            }

            if ((int) $user['status'] !== 1) {
                $this->logger->notice("OAuth login rejected (account locked)", [
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'timestamp' => $dateNow,
                ]);
                return ['status' => 403, 'body' => ['error' => 'account_locked']];
            }

            $payload = [
                'sub' => $user['id'],
                'email' => $user['email'],
                'exp' => time() + 3600
            ];

            $token = JWT::encode($payload, $this->key, 'HS256');
            $tokenHash = hash('sha256', $token);

            $this->db->insert('user_tokens', [
                'user_id' => $user['id'],
                'token_hash' => $tokenHash,
                'creation_date' => $dateNow,
            ]);

            $sessionKey = MiddlewareHelper::sanitizeCacheKey('session', $tokenHash);

            $usuario = [
                'id' => $user['id'],
                'name' => trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'email' => $user['email'],
                'role_id' => $user['role_id'] ?? null
            ];

            $this->cache->set($sessionKey, $usuario, 7200);

            $this->db->update('users', ['last_login' => $dateNow], ['id' => $user['id']]);

            $this->logger->info("OAuth login successful", [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'timestamp' => $dateNow,
            ]);

            return [
                'status' => 200,
                'body' => [
                    'success' => 'login_successful',
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'name' => trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                        'avatar' => $user['avatar'] ?? null,
                        'email' => $user['email'],
                        'status' => $user['status'],
                        'last_login' => $user['last_login']
                    ]
                ]
            ];

        } catch (\Throwable $e) {
            $this->logger->error("OAuth login internal_error", [
                'exception' => $e,
                'email' => $email,
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

}
