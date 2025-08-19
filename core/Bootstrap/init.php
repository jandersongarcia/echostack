<?php

use Core\Services\LoggerFactory;
use Medoo\Medoo;
use Utils\ResponseHelper;
use Core\Utils\Front\FrontPageScript;

require_once ROOT . '/vendor/autoload.php';

$envPath = ROOT . '/.env';

if (!file_exists($envPath)) {
    $logger = LoggerFactory::create();
    $logger->critical('.env file not found', [
        'exception' => new RuntimeException('The ".env" file is required. Please rename ".env.example" to ".env".')
    ]);

    ResponseHelper::jsonErrorResponse([
        'error' => 'Environment file not found',
        'message' => 'The ".env" file is required. Please rename ".env.example" to ".env" and configure your environment variables.',
        'code' => 'E001'
    ]);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->safeLoad();

$logger = LoggerFactory::create();

// Verifica se o acesso da URI é por v0
if (isset($_SERVER['REQUEST_URI']) && preg_match('#^/v0(/.*)?$#', $_SERVER['REQUEST_URI'])) {
    require_once ROOT . '/routes/v0.php'; // Carrega as rotas da versão 0
}

// Se o sistema não estiver instalado, exibe a tela de instalação
if ($_ENV['ECHO_INSTALLED'] === 'false') {
    (new FrontPageScript())->createPage('install');
    ResponseHelper::jsonErrorResponse([
        'error' => 'System not installed',
        'message' => 'The system is not installed. Please complete the installation process.',
        'code' => 'E002'
    ]);
    exit;
} else {
    if(file_exists(ROOT . '/app/index.html')) {
        (new FrontPageScript())->createPage('index'); // Cria a página de front-end
    }
    
    if(file_exists(ROOT . '/app/install.html')) {
        unlink(ROOT . '/app/install.html'); // Remove o instalador após a instalação
    }
}

$driver = strtolower($_ENV['DB_DRIVER'] ?? 'none');
$requiredVars = [];

switch ($driver) {
    case 'mysql':
    case 'pgsql':
        $requiredVars = ['DB_NAME', 'DB_HOST', 'DB_USER', 'DB_PASS'];
        break;
    case 'sqlite':
        $requiredVars = ['DB_NAME'];
        break;
    case 'none':
        $database = null;
        $dbConfig = null;
        break;
    default:
        $logger->error("Unsupported DB_DRIVER: {$driver}");
        ResponseHelper::jsonErrorResponse([
            'error' => 'Invalid DB_DRIVER',
            'message' => "The database driver '{$driver}' is not supported.",
            'code' => 'E003'
        ]);
        exit;
}

foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        $logger->error("Missing environment variable: {$var}", [
            'exception' => new InvalidArgumentException("Missing env var: {$var}")
        ]);

        (new FrontPageScript())->createPage('install');

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            if (preg_match('/^ECHO_INSTALLED\s*=.*$/m', $envContent)) {
                $envContent = preg_replace('/^ECHO_INSTALLED\s*=.*$/m', 'ECHO_INSTALLED=false', $envContent);
            } else {
                $envContent .= PHP_EOL . 'ECHO_INSTALLED=false' . PHP_EOL;
            }

            $envContent = preg_replace("/(\R){3,}/", PHP_EOL . PHP_EOL, $envContent);
            $envContent = rtrim($envContent) . PHP_EOL;

            file_put_contents($envPath, $envContent);
        }

        ResponseHelper::jsonErrorResponse([
            'error' => 'Missing environment variable',
            'message' => "The environment variable '{$var}' is missing or empty in your .env file.",
            'code' => 'E002'
        ]);
        exit;
    }
}

if ($driver !== 'none') {
    $dbConfig = match ($driver) {
        'mysql', 'pgsql' => [
            'database_type' => $driver,
            'database_name' => $_ENV['DB_NAME'],
            'server' => $_ENV['DB_HOST'],
            'username' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASS'],
            'charset' => 'utf8',
            'port' => $_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? 5432 : 3306),
        ],
        'sqlite' => [
            'database_type' => 'sqlite',
            'database_file' => ROOT . $_ENV['DB_NAME'] . '.sqlite'
        ],
        default => []
    };

    try {
        $database = new Medoo($dbConfig);

        if (filter_var($_ENV['DB_PING_ON_START'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $pdo = $database->pdo;
            $pdo->query('SELECT 1');
        }
    } catch (Throwable $e) {
        $logger->critical('Database connection failed', [
            'exception' => $e
        ]);

        ResponseHelper::jsonErrorResponse([
            'error' => 'Database connection failed',
            'message' => $e->getMessage(),
            'code' => 'E100',
            'category' => 'critical'
        ]);
        exit;
    }
}

$appConfig = [
    'app_env' => $_ENV['APP_ENV'] ?? 'production',
    'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
];

return [
    'driver' => $driver,
    'db' => $dbConfig ?? null,
    'app' => $appConfig,
    'database' => $database ?? null,
    'logger' => $logger,
];
