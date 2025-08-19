<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once 'helper-script.php';

use Dotenv\Dotenv;
use Medoo\Medoo;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$langCode = strtolower($_ENV['LANGUAGE'] ?? 'en');
$langFile = dirname(__DIR__, 2) . "/core/lang/{$langCode}.php";
$lang = file_exists($langFile) ? require $langFile : [];
$msg = $lang['make:crud'] ?? [];

$driver = strtolower($_ENV['DB_DRIVER'] ?? 'none');
if ($driver === 'none') {
    out('ERROR', $msg['no_driver'] ?? "DB_DRIVER está como 'none'", 'red');
    exit;
}

$table = $argv[1] ?? null;
$versionInput = $argv[2] ?? null;

if (!$table || !$versionInput) {
    out('WARNING', $msg['usage'] ?? "Uso incorreto.", 'yellow');
    exit;
}

$version = ucfirst(strtolower(trim($versionInput))); // Ex: v1 => V1
$namespace = "App\\{$version}";

$database = new Medoo([
    'type' => $driver,
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4'
]);

switch ($driver) {
    case 'pgsql':
        $stmt = $database->pdo->prepare("SELECT column_name AS Field, data_type AS Type FROM information_schema.columns WHERE table_name = :table");
        $stmt->execute(['table' => $table]);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'sqlite':
        $stmt = $database->pdo->query("PRAGMA table_info($table)");
        $columns = array_map(fn($col) => ['Field' => $col['name'], 'Type' => $col['type']], $stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
    case 'mysql':
    default:
        $columns = $database->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
}

if (!$columns) {
    out('ERROR', str_replace(':table', $table, $msg['table_not_found'] ?? 'Tabela não encontrada.'), 'red');
    exit;
}

function swaggerType(string $sqlType): string {
    $t = strtolower($sqlType);
    return match(true) {
        str_contains($t, 'int') => 'integer',
        str_contains($t, 'bool') => 'boolean',
        str_contains($t, 'float'), str_contains($t, 'decimal'), str_contains($t, 'double') => 'number',
        str_contains($t, 'date'), str_contains($t, 'time') => 'string',
        default => 'string',
    };
}

$className = ucfirst(rtrim($table, 's'));
$modelName = $className;
$serviceName = "{$className}Service";
$controllerName = "{$className}Controller";

$basePath = dirname(__DIR__, 1) . "/../app/{$version}";
@mkdir("{$basePath}/Models", 0775, true);
@mkdir("{$basePath}/Services", 0775, true);
@mkdir("{$basePath}/Controllers", 0775, true);

$modelPath = "$basePath/Models/{$modelName}.php";
$servicePath = "$basePath/Services/{$serviceName}.php";
$controllerPath = "$basePath/Controllers/{$controllerName}.php";

if (file_exists($modelPath) || file_exists($servicePath) || file_exists($controllerPath)) {
    out('ERROR', str_replace([':table', ':version'], [$table, $version], $msg['already_exists'] ?? 'CRUD já existe.'), 'red');
    exit;
}

$schemaProps = [];
foreach ($columns as $col) {
    if (in_array(strtolower($col['Field']), ['senha', 'password', 'token'])) continue;
    $type = swaggerType($col['Type']);
    $schemaProps[] = " *     @OA\\Property(property=\"{$col['Field']}\", type=\"{$type}\"),";
}
$swaggerSchema = "/**\n * @OA\\Schema(\n *     schema=\"{$className}\",\n *     type=\"object\",\n" . implode("\n", $schemaProps) . "\n * )\n */";

out('INFO', str_replace(':version', $version, $msg['version_normalized'] ?? 'Versão normalizada: :version'), 'blue');
out('SUCCESS', str_replace([':table', ':version'], [$table, $version], $msg['success'] ?? 'CRUD criado com sucesso.'), 'green');

out('INFO', $msg['swagger_running'] ?? 'Gerando Swagger...', 'cyan');
echo shell_exec("composer swagger:build");
out('SUCCESS', $msg['swagger_done'] ?? 'Swagger gerado com sucesso.', 'green');