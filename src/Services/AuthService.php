<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Medoo\Medoo;
use Core\Services\LoggerFactory;
use Core\Helpers\MiddlewareHelper;
use Core\Utils\MailHelper;
use Psr\Log\LoggerInterface;

class AuthService
{
    protected Medoo $db;
    private string $key;
    private LoggerInterface $logger;
    private $mailer;
    private $timezone;

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

        $this->key = $_ENV['JWT_SECRET'] ?? $_ENV['JWT_SECRET'];

        date_default_timezone_set($_ENV['TIME_ZONE'] ?? 'UTC');

        $this->mailer = new MailHelper();

        $this->logger = LoggerFactory::create();

        if (!$this->key || !is_string($this->key)) {
            $this->logAndNotify("JWT_SECRET not properly defined in .env file.");
            throw new \RuntimeException("JWT_SECRET is not correctly set in the .env file.");
        }

        $this->timezone = new \DateTimeZone($_ENV['TIME_ZONE'] ?? 'UTC');
    }

    private function logAndNotify(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function login($email, $password)
    {
        try {
            $user = $this->db->get('users', '*', ['email' => $email]);

            $dateNow = (new \DateTime('now', $this->timezone))->format('Y-m-d H:i:s');

            if (!$user || !password_verify($password, $user['password'])) {
                $this->logger->warning("Login failed", [
                    'email_attempted' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'timestamp' => $dateNow,
                ]);
                return ['status' => 401, 'body' => ['error' => 'invalid_credentials']];
            }

            if ($user['status'] != 1) {
                $this->logger->notice("Login rejected (account locked)", [
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'timestamp' => $dateNow,
                ]);
                return ['status' => 401, 'body' => ['error' => 'account_locked']];
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

            if (!$this->db->id()) {
                $this->logger->error("Failed to register token.", [
                    'user_id' => $user['id'],
                    'token_hash' => $tokenHash,
                    'token_size' => strlen($token),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
            } else {
                $this->logger->info("Token successfully registered.", [
                    'user_id' => $user['id'],
                    'token_hash' => $tokenHash,
                ]);
            }

            // Salvar no cache
            $sessionKey = MiddlewareHelper::sanitizeCacheKey('session', $tokenHash);

            $usuario = [
                'id' => $user['id'],
                'name' => $user['name'] . ' ' . $user['last_name'],
                'email' => $user['email'],
                'role_id' => $user['role_id'] ?? null
            ];

            // Inicialize CacheService
            $cache = new \Core\Services\CacheService();
            $cache->set($sessionKey, $usuario, 7200);

            $this->logger->info("Session cache created for token.", [
                'session_key' => $sessionKey,
                'user_id' => $user['id']
            ]);

            $this->db->update('users', ['last_login' => $dateNow], ['id' => $user['id']]);

            $this->logger->info("Login successful.", [
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
                        'name' => $user['name'] . ' ' . $user['last_name'],
                        'role_id' => $user['role_id'] ?? null,
                        'avatar' => $user['avatar'] ?? null,
                        'email' => $user['email'],
                        'status' => $user['status'],
                        'last_login' => $user['last_login']
                    ]
                ]
            ];

        } catch (\Throwable $e) {
            $this->logAndNotify("internal_error", ['exception' => $e]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function register($firstName, $lastName, $email, $password)
    {
        try {
            if ($this->db->has('users', ['email' => $email])) {
                return ['status' => 409, 'body' => ['error' => 'email_existing']];
            }

            $this->db->insert('users', [
                'name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'creation_date' => date('Y-m-d H:i:s')
            ]);

            return ['status' => 201, 'body' => ['success' => 'user_created']];
        } catch (\Throwable $e) {
            $this->logAndNotify("Erro no register", ['exception' => $e]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function forgotPassword($email, $lang = '')
    {
        try {
            $user = $this->db->get("users", "*", ["email" => $email]);

            if (!$user) {
                $this->logger->info("Password recovery failed: email not found.", [
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
                return ['status' => 404, 'body' => ['error' => 'user_not_found']];
            }

            // Geração do token
            $token = bin2hex(random_bytes(16));

            $this->db->insert('password_resets', [
                'email' => $email,
                'token' => $token,
                'expiration' => date(
                    'Y-m-d H:i:s',
                    time() + (int) ($_ENV['PASSWORD_RESET_EXPIRATION'] ?? 7200)
                )
            ]);

            $this->logger->notice("Password recovery token generated.", [
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Carrega template de e-mail
            $templatePath = __DIR__ . '/../Views/emails/recover-template.php';
            if (!file_exists($templatePath)) {
                throw new \Exception("Email template file not found.");
            }

            $name = $user['name'] ?? 'User';
            $expirationSeconds = (int) ($_ENV['PASSWORD_RESET_EXPIRATION'] ?? 7200);
            $expirationHours = floor($expirationSeconds / 3600);
            $link = ($_ENV['APP_URL'] ?? 'http://localhost:5173') . '/account/recovery?token=' . $token;
            $link = str_replace('//', '/', $link); // segurança: remove barras extras

            // Busca pelo template no idioma
            $lang = preg_replace('/[^a-z]/', '', strtolower($lang)); // segurança
            $templateBase = __DIR__ . '/../Views/emails/recover-template';
            $templatePath = $templateBase . ($lang ? ".$lang" : '') . '.php';

            // fallback: default (sem extensão)
            if (!file_exists($templatePath)) {
                $templatePath = $templateBase . '.php';
            }

            ob_start();
            include $templatePath;
            $emailBody = ob_get_clean();

            $this->mailer->send($email, 'Password Recovery', $emailBody);

            return ['status' => 200, 'body' => ['success' => 'email_sent']];

        } catch (\Throwable $e) {
            $this->logAndNotify("Error in forgotPassword.", [
                'exception' => $e,
                'email' => $email
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function validateResetToken($token)
    {
        try {
            $reset = $this->db->get('password_resets', '*', ['token' => $token]);

            if (!$reset || strtotime($reset['expiration']) < time()) {
                return ['status' => 400, 'body' => ['error' => 'invalid_or_expired']];
            }

            return ['status' => 200, 'body' => ['valid' => true]];
        } catch (\Throwable $e) {
            $this->logAndNotify("Error in validateResetToken.", [
                'exception' => $e,
                'token' => $token
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }


    public function resetPassword($token, $newPassword)
    {
        try {
            $reset = $this->db->get('password_resets', '*', ['token' => $token]);

            if (!$reset || strtotime($reset['expiration']) < time()) {
                $this->logger->warning("Reset password failed: invalid or expired token.", [
                    'token' => $token,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                return ['status' => 400, 'body' => ['error' => 'invalid_or_expired']];
            }

            $this->db->update('users', [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ], [
                'email' => $reset['email']
            ]);

            $this->db->delete('password_resets', ['token' => $token]);

            return ['status' => 200, 'body' => ['success' => 'password_reset_successful']];
        } catch (\Throwable $e) {
            $this->logAndNotify("Error in resetPassword.", [
                'exception' => $e,
                'token' => $token
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function logout(): array
    {

        try {

            file_put_contents(
                __DIR__ . "/logout_debug.log",
                "Logout method called at " . date('c') . "
",
                FILE_APPEND
            );

            // 1. Extrair o token do header Authorization
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? '';

            if (!str_starts_with($authHeader, 'Bearer ')) {
                return ['status' => 401, 'body' => ['error' => 'missing_token']];
            }

            $token = trim(substr($authHeader, 7));
            $tokenHash = hash('sha256', $token); // usado apenas para log, sem expor JWT

            // Logar token e tokenHash
            $this->logger->info("Logout requested.", [
                'token' => $token,
                'token_hash' => $tokenHash,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);

            // 2. Buscar o token no banco para verificar se existe e pegar user_id
            $tokenData = $this->db->get('user_tokens', [
                'id',
                'user_id',
                'revoked'
            ], [
                'token_hash' => $tokenHash
            ]);

            if (!$tokenData) {
                $this->logger->warning("Logout: token not found.", [
                    'token_hash' => $tokenHash,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
                return ['status' => 401, 'body' => ['error' => 'token_not_found']];
            }

            // 3. Se já estiver revogado, loga e responde normalmente
            if ($tokenData['revoked'] == 1) {
                $this->logger->info("Logout: token was already revoked earlier.", [
                    'user_id' => $tokenData['user_id'],
                    'token_hash' => $tokenHash
                ]);
                return ['status' => 200, 'body' => ['success' => 'already_revoked']];
            }

            // 4. Atualiza o token como revogado
            $revokedAt = (new \DateTime('now', $this->timezone))->format('Y-m-d H:i:s');

            $this->db->update('user_tokens', [
                'revoked' => 1,
                'revoked_at' => $revokedAt
            ], [
                'id' => $tokenData['id']
            ]);

            // 5. Log do logout bem-sucedido
            $this->logger->info("Logout completed successfully.", [
                'user_id' => $tokenData['user_id'],
                'token_hash' => $tokenHash,
                'revoked_at' => $revokedAt
            ]);

            // Limpa o cache do token
            $sessionKey = MiddlewareHelper::sanitizeCacheKey('session', $tokenHash);
            $cache = new \Core\Services\CacheService();
            $cache->delete($sessionKey);

            $this->logger->info("Session cache invalidated on logout.", [
                'session_key' => $sessionKey,
                'user_id' => $tokenData['user_id']
            ]);


            return ['status' => 200, 'body' => ['success' => 'logout_successful']];
        } catch (\Throwable $e) {
            $this->logAndNotify("logout_error", ['exception' => $e]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function getAuthenticatedUser(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return ['status' => 401, 'body' => ['error' => 'missing_token']];
        }

        $token = trim(substr($authHeader, 7));
        $tokenHash = hash('sha256', $token);

        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            $userId = $decoded->sub ?? null;

            if (!$userId) {
                return ['status' => 401, 'body' => ['error' => 'invalid_token']];
            }

            $tokenData = $this->db->get('user_tokens', '*', [
                'token_hash' => $tokenHash
            ]);

            if (!$tokenData) {
                return ['status' => 401, 'body' => ['error' => 'token_not_found']];
            }

            if ((int) ($tokenData['revoked'] ?? 0) === 1) {
                return ['status' => 401, 'body' => ['error' => 'token_revoked']];
            }

            $user = $this->db->get('users', '*', ['id' => $userId]);

            if (!$user) {
                return ['status' => 404, 'body' => ['error' => 'user_not_found']];
            }

            return [
                'status' => 200,
                'body' => [
                    'id' => $user['id'],
                    'name' => trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                    'role_id' => $user['role_id'] ?? null,
                    'avatar' => $user['avatar'] ?? null,
                    'email' => $user['email'],
                    'status' => $user['status'],
                    'last_login' => $user['last_login']
                ]
            ];
        } catch (\Throwable $e) {
            $this->logger->error("auth/me error", ['exception' => $e]);
            return ['status' => 401, 'body' => ['error' => 'invalid_token']];
        }
    }


}

