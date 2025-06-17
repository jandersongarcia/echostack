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

// 2. Ensure SECRET_KEY exists in .env
out('INFO', 'Checking SECRET_KEY in .env...');
$envPath = DIR . '/.env';
$envContent = file_get_contents($envPath);
if (!str_contains($envContent, 'SECRET_KEY=')) {
    out('WARNING', 'SECRET_KEY not found. Generating with composer generate:key...', 'yellow');
    exec('composer generate:key');
} else {
    out('INFO', 'SECRET_KEY already exists.');
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
    public static function login() {
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
     *       required={"name","email","password"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=201, description="User created"),
     *   @OA\Response(response=400, description="Email already registered")
     * )
     */
    public static function register() {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$result = (new AuthService())->register(\$input['name'] ?? '', \$input['email'] ?? '', \$input['password'] ?? '');
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
     *       @OA\Property(property="email", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Reset token sent")
     * )
     */
    public static function forgotPassword() {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$result = (new AuthService())->forgotPassword(\$input['email'] ?? '');
        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }

    /**
     * @OA\Post(
     *   path="/auth/reset-password",
     *   summary="Reset password with recovery token",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"token","new_password"},
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="new_password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Password reset"),
     *   @OA\Response(response=400, description="Invalid or expired token")
     * )
     */
    public static function resetPassword() {
        \$input = json_decode(file_get_contents('php://input'), true);
        \$result = (new AuthService())->resetPassword(\$input['token'] ?? '', \$input['new_password'] ?? '');
        (new JsonResponse(\$result['body'], \$result['status']))->send();
    }

    /**
     * @OA\Post(
     *   path="/auth/logout",
     *   summary="Logout (JWT)",
     *   tags={"Auth"},
     *   security={{"bearerAuth": {}}},
     *   @OA\Response(response=200, description="Logged out")
     * )
     */
    public static function logout() {
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
use Firebase\JWT\Key;
use Medoo\Medoo;

class AuthService
{
    protected \$db;
    private \$key;

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

        \$this->key = getenv('SECRET_KEY');
    }

    public function login(\$email, \$password)
    {
        \$user = \$this->db->get('users', '*', ['email' => \$email]);
        if (!\$user || !password_verify(\$password, \$user['password'])) {
            return ['status' => 401, 'body' => ['error' => 'Invalid credentials']];
        }

        \$payload = [
            'sub' => \$user['id'],
            'email' => \$user['email'],
            'exp' => time() + 3600
        ];

        \$token = JWT::encode(\$payload, \$this->key, 'HS256');
        return ['status' => 200, 'body' => ['token' => \$token]];
    }

    public function register(\$username, \$email, \$password)
    {
        \$exists = \$this->db->has('users', ['email' => \$email]);
        if (\$exists) {
            return ['status' => 400, 'body' => ['error' => 'Email already registered']];
        }

        \$this->db->insert('users', [
            'user' => \$username,
            'email' => \$email,
            'password' => password_hash(\$password, PASSWORD_DEFAULT)
        ]);

        return ['status' => 201, 'body' => ['success' => 'User created']];
    }

    public function forgotPassword(\$email)
    {
        \$token = bin2hex(random_bytes(16));

        \$this->db->insert('password_resets', [
            'email' => \$email,
            'token' => \$token,
            'expiration' => date('Y-m-d H:i:s', time() + 3600)
        ]);

        return ['status' => 200, 'body' => ['token_reset' => \$token]];
    }

    public function resetPassword(\$token, \$newPassword)
    {
        \$reset = \$this->db->get('password_resets', '*', ['token' => \$token]);
        if (!\$reset || strtotime(\$reset['expiration']) < time()) {
            return ['status' => 400, 'body' => ['error' => 'Invalid or expired token']];
        }

        \$this->db->update('users', [
            'password' => password_hash(\$newPassword, PASSWORD_DEFAULT)
        ], [
            'email' => \$reset['email']
        ]);

        \$this->db->delete('password_resets', ['token' => \$token]);

        return ['status' => 200, 'body' => ['success' => 'Password reset successful']];
    }

    public function logout()
    {
        // No JWT stateless logout (client discards token)
        return ['status' => 200, 'body' => ['success' => 'Logout successful']];
    }
}

PHP
);
out('INFO', 'AuthService created.');

// 5. Criar JWT Middleware
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
            \$key = getenv('SECRET_KEY');
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
out('INFO', 'JwtAuthMiddleware created.');

// 6. Run SQL migration from core/migration/auth-migrations.sql
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


// 7. Adicionar rotas no web.php
$routeFile = DIR . '/routes/web.php';
$existingRoutes = file_get_contents($routeFile);

$routesToAdd = [
    "// Auto-generated CRUD routes for Auth",
    "\$router->map('POST', '/auth/login', 'AuthController#login');",
    "\$router->map('POST', '/auth/register', 'AuthController#register');",
    "\$router->map('POST', '/auth/forgot-password', 'AuthController#forgotPassword');",
    "\$router->map('POST', '/auth/reset-password', 'AuthController#resetPassword');",
    "\$router->map('POST', '/auth/logout', 'AuthController#logout', ['middleware' => 'JwtAuth']);"
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

out('INFO', 'running swagger:build');
echo shell_exec("composer swagger:build");

out('SUCCESS', 'Auth installation completed successfully!', 'green');
