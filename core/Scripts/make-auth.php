<?php

/**
 * Script: install-auth.php
 * Purpose: Full JWT Auth installation for EchoAPI with Swagger annotations, SQL migrations, and route setup.
 */

define('DIR', dirname(__DIR__, 2));
require_once DIR . '/vendor/autoload.php';
require_once('helper-script.php');

use Dotenv\Dotenv;
use Medoo\Medoo;

// Carrega variáveis do .env
$dotenv = Dotenv::createImmutable(DIR);
$dotenv->load();

// Conexão com o banco via Medoo
$database = new Medoo([
    'type' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4'
]);

// 1. Ensure firebase/php-jwt is installed
//out('INFO', 'Checking firebase/php-jwt...');
//exec('composer require firebase/php-jwt');

// 2. Ensure JWT_SECRET exists in .env
out('INFO', 'Checking JWT_SECRET in .env...');
$envPath = DIR . '/.env';
$envContent = file_get_contents($envPath);
if (!str_contains($envContent, 'JWT_SECRET=')) {
    out('WARNING', 'JWT_SECRET not found. Generating with composer generate:key...', 'yellow');
    exec('composer generate:key');
} else {
    out('INFO', 'JWT_SECRET already exists.');
}

// 3. Create AuthController with Swagger annotations
file_put_contents(
    DIR . '/src/Controllers/AuthController.php',
    <<<PHP
<?php

namespace App\Controllers;

use App\Services\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @OA\Tag(name="AuthController")
 */
class AuthController
{
    /**
     * @OA\Post(
     *   path="/auth/login",
     *   summary="User Login (JWT)",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="JWT token generated"),
     *   @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public static function login()
    {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$result = (new AuthService())->login(\$input['email'] ?? '', \$input['password'] ?? '');
        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }

    /**
     * @OA\Post(
     *   path="/auth/register",
     *   summary="Register new user",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name", "last_name", "email", "password"},
     *       @OA\Property(property="name", type="string", example="Janderson"),
     *       @OA\Property(property="last_name", type="string", example="Ganjos"),
     *       @OA\Property(property="email", type="string", example="jganjos.info@gmail.com"),
     *       @OA\Property(property="password", type="string", format="password", example="SenhaForte123!")
     *     )
     *   ),
     *   @OA\Response(response=201, description="User created"),
     *   @OA\Response(response=400, description="Email already registered")
     * )
     */
    public static function register()
    {
        \$input = json_decode(file_get_contents('php://input'), true);

        \$name = \$input['name'] ?? '';
        \$lastName = \$input['last_name'] ?? '';
        \$email = \$input['email'] ?? '';
        \$password = \$input['password'] ?? '';

        \$result = (new AuthService())->register(\$name, \$lastName, \$email, \$password);

        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }


    /**
     * @OA\Post(
     *   path="/auth/forgot-password",
     *   summary="Request password recovery",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *       @OA\Property(property="lang", type="string", example="en", description="Optional language code for the email template")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Password recovery email sent",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="email_sent")
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Invalid email provided",
     *     @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="invalid_email")
     *     )
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="User not found",
     *     @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="user_not_found")
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal server error",
     *     @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="internal_error")
     *     )
     *   )
     * )
     */
    public static function forgotPassword()
    {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$email = trim(\$input['email'] ?? '');
        \$lang = strtolower(\$input['lang'] ?? '');
        if (!\$email) {
            (new JsonResponse(['error' => 'invalid_email'], 400))->send();
            return;
        }
        \$result = (new AuthService())->forgotPassword(\$email, \$lang);
        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }


    /**
     * @OA\Get(
     *   path="/auth/reset-password",
     *   summary="Validate password recovery token",
     *   tags={"Auth"},
     *   @OA\Parameter(
     *     name="token",
     *     in="query",
     *     required=true,
     *     description="Password recovery token",
     *     @OA\Schema(type="string", example="a1b2c3d4e5")
     *   ),
     *   @OA\Response(response=200, description="Token is valid"),
     *   @OA\Response(response=400, description="Invalid or expired token")
     * )
     */

    /**
     * @OA\Post(
     *   path="/auth/reset-password",
     *   summary="Reset password with recovery token",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"token","new_password"},
     *       @OA\Property(property="token", type="string", example="a1b2c3d4e5"),
     *       @OA\Property(property="new_password", type="string", example="NovaSenha123!")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Password reset"),
     *   @OA\Response(response=400, description="Invalid or expired token")
     * )
     */

    public static function resetPassword()
    {
        if (\$_SERVER['REQUEST_METHOD'] === 'GET') {
            \$token = \$_GET['token'] ?? '';
            \$result = (new AuthService())->validateResetToken(\$token);
        } else {
            \$input = json_decode(file_get_contents('php://input'), true);
            \$result = (new AuthService())->resetPassword(\$input['token'] ?? '', \$input['new_password'] ?? '');
        }

        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }


    /**
     * @OA\Post(
     *   path="/auth/logout",
     *   summary="Logout do usuário autenticado",
     *   description="Revoga o token JWT atual e encerra a sessão do usuário.",
     *   tags={"Auth"},
     *   security={{"bearerAuth": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Logout bem-sucedido",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="string", example="logout_successful")
     *     )
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Token ausente ou revogado",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="error", type="string", example="token_revoked")
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Erro interno no servidor",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="error", type="string", example="internal_error")
     *     )
     *   )
     * )
     */
    public static function logout()
    {
        \$result = (new AuthService())->logout();
        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }

}
PHP
);

out('INFO', 'AuthController created.');

// 4. Criar AuthService
file_put_contents(
    DIR . '/src/Services/AuthService.php',
    <<<PHP
<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Medoo\Medoo;
use Core\Services\LoggerFactory;
use Core\Helpers\MiddlewareHelper;
use Core\Utils\MailHelper;
use Psr\Log\LoggerInterface;

class AuthService
{
    protected Medoo \$db;
    private string \$key;
    private LoggerInterface \$logger;
    private \$mailer;
    private \$timezone;

    public function __construct()
    {
        \$this->db = new Medoo([
            'type' => 'mysql',
            'host' => \$_ENV['DB_HOST'],
            'database' => \$_ENV['DB_NAME'],
            'username' => \$_ENV['DB_USER'],
            'password' => \$_ENV['DB_PASS'],
            'port' => \$_ENV['DB_PORT'] ?? 3306,
            'charset' => 'utf8mb4'
        ]);

        \$this->key = \$_ENV['JWT_SECRET'] ?? \$_ENV['JWT_SECRET'];

        date_default_timezone_set(\$_ENV['TIME_ZONE'] ?? 'UTC');

        \$this->mailer = new MailHelper();

        \$this->logger = LoggerFactory::create();

        if (!\$this->key || !is_string(\$this->key)) {
            \$this->logAndNotify("JWT_SECRET not properly defined in .env file.");
            throw new \RuntimeException("JWT_SECRET is not correctly set in the .env file.");
        }

        \$this->timezone = new \DateTimeZone(\$_ENV['TIME_ZONE'] ?? 'UTC');
    }

    private function logAndNotify(string \$message, array \$context = []): void
    {
        \$this->logger->error(\$message, \$context);
    }

    public function login(\$email, \$password)
    {
        try {
            \$user = \$this->db->get('users', '*', ['email' => \$email]);

            \$dateNow = (new \DateTime('now', \$this->timezone))->format('Y-m-d H:i:s');

            if (!\$user || !password_verify(\$password, \$user['password'])) {
                \$this->logger->warning("Login failed", [
                    'email_attempted' => \$email,
                    'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'timestamp' => \$dateNow,
                ]);
                return ['status' => 401, 'body' => ['error' => 'invalid_credentials']];
            }

            if (\$user['status'] != 1) {
                \$this->logger->notice("Login rejected (account locked)", [
                    'user_id' => \$user['id'],
                    'email' => \$user['email'],
                    'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'timestamp' => \$dateNow,
                ]);
                return ['status' => 401, 'body' => ['error' => 'account_locked']];
            }

            \$payload = [
                'sub' => \$user['id'],
                'email' => \$user['email'],
                'exp' => time() + 3600
            ];

            \$token = JWT::encode(\$payload, \$this->key, 'HS256');
            \$tokenHash = hash('sha256', \$token);

            \$this->db->insert('user_tokens', [
                'user_id' => \$user['id'],
                'token_hash' => \$tokenHash,
                'creation_date' => \$dateNow,
            ]);

            if (!\$this->db->id()) {
                \$this->logger->error("Failed to register token.", [
                    'user_id' => \$user['id'],
                    'token_hash' => \$tokenHash,
                    'token_size' => strlen(\$token),
                    'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
            } else {
                \$this->logger->info("Token successfully registered.", [
                    'user_id' => \$user['id'],
                    'token_hash' => \$tokenHash,
                ]);
            }

            // Salvar no cache
            \$sessionKey = MiddlewareHelper::sanitizeCacheKey('session', \$tokenHash);

            \$usuario = [
                'id' => \$user['id'],
                'name' => \$user['name'] . ' ' . \$user['last_name'],
                'email' => \$user['email'],
                'role_id' => \$user['role_id'] ?? null
            ];

            // Inicialize CacheService
            \$cache = new \Core\Services\CacheService();
            \$cache->set(\$sessionKey, \$usuario, 7200);

            \$this->logger->info("Session cache created for token.", [
                'session_key' => \$sessionKey,
                'user_id' => \$user['id']
            ]);

            \$this->db->update('users', ['last_login' => \$dateNow], ['id' => \$user['id']]);

            \$this->logger->info("Login successful.", [
                'user_id' => \$user['id'],
                'email' => \$user['email'],
                'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'timestamp' => \$dateNow,
            ]);

            return [
                'status' => 200,
                'body' => [
                    'success' => 'login_successful',
                    'token' => \$token,
                    'user' => [
                        'id' => \$user['id'],
                        'name' => \$user['name'] . ' ' . \$user['last_name'],
                        'avatar' => \$user['avatar'] ?? null,
                        'email' => \$user['email'],
                        'status' => \$user['status'],
                        'last_login' => \$user['last_login']
                    ]
                ]
            ];

        } catch (\Throwable \$e) {
            \$this->logAndNotify("internal_error", ['exception' => \$e]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function register(\$firstName, \$lastName, \$email, \$password)
    {
        try {
            if (\$this->db->has('users', ['email' => \$email])) {
                return ['status' => 409, 'body' => ['error' => 'email_existing']];
            }

            \$this->db->insert('users', [
                'name' => \$firstName,
                'last_name' => \$lastName,
                'email' => \$email,
                'password' => password_hash(\$password, PASSWORD_DEFAULT),
                'creation_date' => date('Y-m-d H:i:s')
            ]);

            return ['status' => 201, 'body' => ['success' => 'user_created']];
        } catch (\Throwable \$e) {
            \$this->logAndNotify("Erro no register", ['exception' => \$e]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function forgotPassword(\$email, \$lang = '')
    {
        try {
            \$user = \$this->db->get("users", "*", ["email" => \$email]);

            if (!\$user) {
                \$this->logger->info("Password recovery failed: email not found.", [
                    'email' => \$email,
                    'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
                return ['status' => 404, 'body' => ['error' => 'user_not_found']];
            }

            // Geração do token
            \$token = bin2hex(random_bytes(16));

            \$this->db->insert('password_resets', [
                'email' => \$email,
                'token' => \$token,
                'expiration' => date(
                    'Y-m-d H:i:s',
                    time() + (int) (\$_ENV['PASSWORD_RESET_EXPIRATION'] ?? 7200)
                )
            ]);

            \$this->logger->notice("Password recovery token generated.", [
                'email' => \$email,
                'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Carrega template de e-mail
            \$templatePath = __DIR__ . '/../Views/emails/recover-template.php';
            if (!file_exists(\$templatePath)) {
                throw new \Exception("Email template file not found.");
            }

            \$name = \$user['name'] ?? 'User';
            \$expirationSeconds = (int) (\$_ENV['PASSWORD_RESET_EXPIRATION'] ?? 7200);
            \$expirationHours = floor(\$expirationSeconds / 3600);
            \$link = (\$_ENV['APP_URL'] ?? 'http://localhost:5173') . '/account/recovery?token=' . \$token;
            \$link = str_replace('//', '/', \$link); // segurança: remove barras extras

            // Busca pelo template no idioma
            \$lang = preg_replace('/[^a-z]/', '', strtolower(\$lang)); // segurança
            \$templateBase = __DIR__ . '/../Views/emails/recover-template';
            \$templatePath = \$templateBase . (\$lang ? ".\$lang" : '') . '.php';

            // fallback: default (sem extensão)
            if (!file_exists(\$templatePath)) {
                \$templatePath = \$templateBase . '.php';
            }

            ob_start();
            include \$templatePath;
            \$emailBody = ob_get_clean();

            \$this->mailer->send(\$email, 'Password Recovery', \$emailBody);

            return ['status' => 200, 'body' => ['success' => 'email_sent']];

        } catch (\Throwable \$e) {
            \$this->logAndNotify("Error in forgotPassword.", [
                'exception' => \$e,
                'email' => \$email
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function validateResetToken(\$token)
    {
        try {
            \$reset = \$this->db->get('password_resets', '*', ['token' => \$token]);

            if (!\$reset || strtotime(\$reset['expiration']) < time()) {
                return ['status' => 400, 'body' => ['error' => 'invalid_or_expired']];
            }

            return ['status' => 200, 'body' => ['valid' => true]];
        } catch (\Throwable \$e) {
            \$this->logAndNotify("Error in validateResetToken.", [
                'exception' => \$e,
                'token' => \$token
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }


    public function resetPassword(\$token, \$newPassword)
    {
        try {
            \$reset = \$this->db->get('password_resets', '*', ['token' => \$token]);

            if (!\$reset || strtotime(\$reset['expiration']) < time()) {
                \$this->logger->warning("Reset password failed: invalid or expired token.", [
                    'token' => \$token,
                    'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                return ['status' => 400, 'body' => ['error' => 'invalid_or_expired']];
            }

            \$this->db->update('users', [
                'password' => password_hash(\$newPassword, PASSWORD_DEFAULT)
            ], [
                'email' => \$reset['email']
            ]);

            \$this->db->delete('password_resets', ['token' => \$token]);

            return ['status' => 200, 'body' => ['success' => 'password_reset_successful']];
        } catch (\Throwable \$e) {
            \$this->logAndNotify("Error in resetPassword.", [
                'exception' => \$e,
                'token' => \$token
            ]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }

    public function logout(): array
    {

        try {

            file_put_contents(
                __DIR__ . "/logout_debug.log",
                "Logout method called at " . date('c') . "\n",
                FILE_APPEND
            );

            // 1. Extrair o token do header Authorization
            \$headers = getallheaders();
            \$authHeader = \$headers['Authorization'] ?? '';

            if (!str_starts_with(\$authHeader, 'Bearer ')) {
                return ['status' => 401, 'body' => ['error' => 'missing_token']];
            }

            \$token = trim(substr(\$authHeader, 7));
            \$tokenHash = hash('sha256', \$token); // usado apenas para log, sem expor JWT

            // Logar token e tokenHash
            \$this->logger->info("Logout requested.", [
                'token' => \$token,
                'token_hash' => \$tokenHash,
                'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);

            // 2. Buscar o token no banco para verificar se existe e pegar user_id
            \$tokenData = \$this->db->get('user_tokens', [
                'id',
                'user_id',
                'revoked'
            ], [
                'token_hash' => \$tokenHash
            ]);

            if (!\$tokenData) {
                \$this->logger->warning("Logout: token not found.", [
                    'token_hash' => \$tokenHash,
                    'ip' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
                return ['status' => 401, 'body' => ['error' => 'token_not_found']];
            }

            // 3. Se já estiver revogado, loga e responde normalmente
            if (\$tokenData['revoked'] == 1) {
                \$this->logger->info("Logout: token was already revoked earlier.", [
                    'user_id' => \$tokenData['user_id'],
                    'token_hash' => \$tokenHash
                ]);
                return ['status' => 200, 'body' => ['success' => 'already_revoked']];
            }

            // 4. Atualiza o token como revogado
            \$revokedAt = (new \DateTime('now', \$this->timezone))->format('Y-m-d H:i:s');

            \$this->db->update('user_tokens', [
                'revoked' => 1,
                'revoked_at' => \$revokedAt
            ], [
                'id' => \$tokenData['id']
            ]);

            // 5. Log do logout bem-sucedido
            \$this->logger->info("Logout completed successfully.", [
                'user_id' => \$tokenData['user_id'],
                'token_hash' => \$tokenHash,
                'revoked_at' => \$revokedAt
            ]);

            // Limpa o cache do token
            \$sessionKey = MiddlewareHelper::sanitizeCacheKey('session', \$tokenHash);
            \$cache = new \Core\Services\CacheService();
            \$cache->delete(\$sessionKey);

            \$this->logger->info("Session cache invalidated on logout.", [
                'session_key' => \$sessionKey,
                'user_id' => \$tokenData['user_id']
            ]);


            return ['status' => 200, 'body' => ['success' => 'logout_successful']];
        } catch (\Throwable \$e) {
            \$this->logAndNotify("logout_error", ['exception' => \$e]);
            return ['status' => 500, 'body' => ['error' => 'internal_error']];
        }
    }
}


PHP
);
out('INFO', 'AuthService created.');

// 6. Criar JWT Middleware
file_put_contents(
    DIR . '/Middleware/JwtAuthMiddleware.php',
    <<<PHP
<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\JsonResponse;

class JwtAuthMiddleware
{
    public static function handle() {
        \$headers = getallheaders();
        \$authHeader = \$headers['Authorization'] ?? '';
        if (!str_starts_with(\$authHeader, 'Bearer ')) {
            (new JsonResponse(['error' => 'Token ausente'], 401))->send();
            exit;
        }
        \$token = trim(str_replace('Bearer ', '', \$authHeader));
        try {
            \$key = \$_ENV('JWT_SECRET');
            \$decoded = JWT::decode(\$token, new Key(\$key, 'HS256'));
            \$_SERVER['user_id'] = \$decoded->sub;
        } catch (\Exception \$e) {
            (new JsonResponse(['error' => 'Token inválido'], 401))->send();
            exit;
        }
    }
}
PHP
);
out('INFO', 'Utils MailHelper created.');

// Verifica se o diretório de emails existe
$templateDir = DIR . '/src/Views/emails';

// Se não existir, cria a pasta (e as intermediárias, se precisar)
if (!is_dir($templateDir)) {
    mkdir($templateDir, 0775, true);
}

// 7. Criar JWT Middleware
file_put_contents(
    DIR . '/src/Views/emails/recover-template.php',
    <<<PHP
<?php
/**
 * Email Template: Password Recovery
 * Atenção: Não altere as variáveis PHP abaixo.
 * As variáveis \$name e \$link são obrigatórias e controladas pela camada de serviço.
 * Modifique apenas o texto ou o layout HTML se quiser personalizar o visual.
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Recovery</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #007BFF;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <p>Hello <?= htmlspecialchars(\$name) ?>,</p>

    <p>We received a request to reset your password.</p>

    <p>Click the button below to choose a new password:</p>

    <p>
        <a href="<?= htmlspecialchars(\$link) ?>" class="button">Reset Password</a>
    </p>

    <p>If you prefer, you can also copy and paste this link into your browser:</p>
    <p><?= htmlspecialchars(\$link) ?></p>

    <p>If you didn’t request this change, you can safely ignore this email. Your account remains secure.</p>

    <div class="footer">
        This is an automated message. Please do not reply.
    </div>
</body>
</html>

PHP
);
out('INFO', 'Utils MailHelper created.');

// 8. Run SQL migration from core/migration/auth-migrations.sql
$sqlFile = DIR . '/core/Migration/auth-migrations.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    try {
        $database->pdo->exec($sql);
        out('SUCCESS', 'Auth tables created in the database.', 'green');
    } catch (PDOException $e) {
        out('ERROR', 'Failed to execute auth-migrations.sql: ' . $e->getMessage(), 'red');
    }
} else {
    out('WARNING', 'auth-migrations.sql not found in core/Migration.', 'yellow');
}

// 9. Adicionar rotas no web.php
$routeFile = DIR . '/routes/web.php';
$existingRoutes = file_get_contents($routeFile);

$routesToAdd = [
    "// Auto-generated CRUD routes for Auth",
    "\$router->map('POST', '/auth/login', 'App\Controllers\AuthController@login');",
    "\$router->map('POST', '/auth/register', 'App\Controllers\AuthController@register');",
    "\$router->map('POST', '/auth/forgot-password', 'App\Controllers\AuthController@forgotPassword');",
    "\$router->map('POST', '/auth/reset-password', 'App\Controllers\AuthController@resetPassword');",
    "\$router->map('GET', '/auth/reset-password', 'App\\Controllers\\AuthController@resetPassword');",
    "\$router->map('POST', '/auth/logout', 'App\Controllers\AuthController@logout');"
];

$newRoutes = "";

foreach ($routesToAdd as $route) {
    if (strpos($existingRoutes, $route) === false) {
        $newRoutes .= PHP_EOL . $route;
    } else {
        out('WARNING', "Route already exists and was skipped: {$route}", 'yellow');
    }
}

if (!empty($newRoutes)) {
    file_put_contents($routeFile, $newRoutes, FILE_APPEND);
    out('SUCCESS', 'Auth routes added to routes/web.php', 'green');
} else {
    out('INFO', 'No new routes were added. All auth routes already exist.');
}

// 10. Adicionar as rotas públicas em public-routes.php
$publicRoutesFile = DIR . '/routes/public-routes.php';

// Garante que o arquivo existe (se não existir, cria com array vazio)
if (!file_exists($publicRoutesFile)) {
    file_put_contents($publicRoutesFile, "<?php\nreturn [];\n");
}

$existingPublicRoutes = include $publicRoutesFile;
if (!is_array($existingPublicRoutes)) {
    $existingPublicRoutes = [];
}

$newPublicRoutes = [
    '/auth/login',
    '/auth/register',
    '/auth/forgot-password',
    '/auth/reset-password'
];

// Faz merge e remove duplicatas
$finalPublicRoutes = array_values(array_unique(array_merge($existingPublicRoutes, $newPublicRoutes)));

// Monta o array em sintaxe short ([]) com cada rota numa linha
$formattedRoutes = "[\n";
foreach ($finalPublicRoutes as $route) {
    $formattedRoutes .= "    '" . addslashes($route) . "',\n";
}
$formattedRoutes .= "];\n";

// Grava o arquivo
file_put_contents($publicRoutesFile, "<?php\nreturn " . $formattedRoutes);

out('SUCCESS', 'Public routes updated in routes/public-routes.php (without overwriting existing ones)', 'green');

out('INFO', 'Running swagger:build');
echo shell_exec("composer swagger:build");

out('SUCCESS', 'Auth installation completed successfully!', 'green');
