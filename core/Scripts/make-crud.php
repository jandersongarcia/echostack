<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once 'helper-script.php';

use Dotenv\Dotenv;
use Medoo\Medoo;

// Load .env variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

// Database connection via Medoo
$database = new Medoo([
    'type' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4'
]);

$table = $argv[1] ?? null;
if (!$table) {
    out('WARNING', "Please provide the table name: composer make:crud table_name", 'yellow');
    exit;
}

$columns = $database->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
if (!$columns) {
    out('ERROR', "Table '$table' not found in the database.", 'red');
    exit;
}

function swaggerType(string $sqlType): string {
    if (str_contains($sqlType, 'int')) return 'integer';
    if (str_contains($sqlType, 'bool')) return 'boolean';
    if (str_contains($sqlType, 'float') || str_contains($sqlType, 'decimal') || str_contains($sqlType, 'double')) return 'number';
    if (str_contains($sqlType, 'date') || str_contains($sqlType, 'time')) return 'string';
    return 'string';
}

// Prepare class names
$className = ucfirst(rtrim($table, 's'));
$controllerName = "{$className}Controller";
$serviceName = "{$className}Service";
$modelName = $className;

$basePath = dirname(__DIR__, 1) . '/../src';
@mkdir("{$basePath}/Models", 0775, true);
@mkdir("{$basePath}/Services", 0775, true);
@mkdir("{$basePath}/Controllers", 0775, true);

$modelPath = "{$basePath}/Models/{$modelName}.php";
$servicePath = "{$basePath}/Services/{$serviceName}.php";
$controllerPath = "{$basePath}/Controllers/{$controllerName}.php";

if (file_exists($modelPath) || file_exists($servicePath) || file_exists($controllerPath)) {
    out('ERROR', "CRUD for '{$table}' already exists. Run 'composer delete:crud {$table}' before generating again.", 'red');
    exit;
}

// Generate Model
file_put_contents($modelPath, "<?php

namespace App\Models;

class {$modelName}
{
" . implode("\n", array_map(fn($col) => "    public \${$col['Field']};", $columns)) . "

    // Customize model methods as needed.
}
");

// Generate Swagger Schema
$schemaProps = [];
foreach ($columns as $col) {
    if (in_array(strtolower($col['Field']), ['senha', 'password', 'token'])) continue;
    $type = swaggerType($col['Type']);
    $schemaProps[] = " *     @OA\Property(property=\"{$col['Field']}\", type=\"{$type}\"),";
}
$swaggerSchema = "/**\n * @OA\Schema(\n *     schema=\"{$className}\",\n *     type=\"object\",\n" . implode("\n", $schemaProps) . "\n * )\n */";

// Generate Service
file_put_contents($servicePath, "<?php

namespace App\Services;

use Medoo\Medoo;

{$swaggerSchema}
class {$serviceName}
{
    protected \$db;

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
    }

    public function list()
    {
        return \$this->db->select('{$table}', '*');
    }

    public function get(\$id)
    {
        return \$this->db->get('{$table}', '*', ['id' => \$id]);
    }

    public function create(array \$data)
    {
        return \$this->db->insert('{$table}', \$data)->rowCount();
    }

    public function update(\$id, array \$data)
    {
        return \$this->db->update('{$table}', \$data, ['id' => \$id])->rowCount();
    }

    public function delete(\$id)
    {
        return \$this->db->delete('{$table}', ['id' => \$id])->rowCount();
    }
}
");

// Generate Controller
file_put_contents($controllerPath, "<?php

namespace App\Controllers;

use App\Services\\{$serviceName};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Tag(name=\"{$className}\")
 */
class {$controllerName}
{
    protected \$service;

    public function __construct()
    {
        \$this->service = new {$serviceName}();
    }

    /**
     * @OA\Get(
     *     path=\"/{$table}\",
     *     tags={\"{$className}\"},
     *     summary=\"List all records\",
     *     @OA\Response(
     *         response=200,
     *         description=\"Successful response\",
     *         @OA\JsonContent(type=\"array\", @OA\Items(ref=\"#/components/schemas/{$className}\"))
     *     )
     * )
     */
    public function index()
    {
        echo json_encode(\$this->service->list());
    }

    /**
     * @OA\Get(
     *     path=\"/{$table}/id\",
     *     tags={\"{$className}\"},
     *     summary=\"Get a single record\",
     *     @OA\Parameter(name=\"id\", in=\"path\", required=true, @OA\Schema(type=\"integer\")),
     *     @OA\Response(response=200, description=\"Success\", @OA\JsonContent(ref=\"#/components/schemas/{$className}\")),
     *     @OA\Response(response=404, description=\"Not found\")
     * )
     */
    public function show(\$id)
    {
        echo json_encode(\$this->service->get(\$id));
    }

    /**
     * @OA\Post(
     *     path=\"/{$table}\",
     *     tags={\"{$className}\"},
     *     summary=\"Create a new record\",
     *     @OA\RequestBody(@OA\JsonContent(ref=\"#/components/schemas/{$className}\")),
     *     @OA\Response(response=201, description=\"Created\")
     * )
     */
    public function store()
    {
        \$data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['created' => \$this->service->create(\$data)]);
    }

    /**
     * @OA\Put(
     *     path=\"/{$table}/id\",
     *     tags={\"{$className}\"},
     *     summary=\"Update a record\",
     *     @OA\Parameter(name=\"id\", in=\"path\", required=true, @OA\Schema(type=\"integer\")),
     *     @OA\RequestBody(@OA\JsonContent(ref=\"#/components/schemas/{$className}\")),
     *     @OA\Response(response=200, description=\"Updated\")
     * )
     */
    public function update(\$id)
    {
        \$data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['updated' => \$this->service->update(\$id, \$data)]);
    }

    /**
     * @OA\Delete(
     *     path=\"/{$table}/id\",
     *     tags={\"{$className}\"},
     *     summary=\"Delete a record\",
     *     @OA\Parameter(name=\"id\", in=\"path\", required=true, @OA\Schema(type=\"integer\")),
     *     @OA\Response(response=200, description=\"Deleted\")
     * )
     */
    public function destroy(\$id)
    {
        echo json_encode(['deleted' => \$this->service->delete(\$id)]);
    }
}
");

// Add routes to routes/web.php
$routePath = dirname(__DIR__, 2) . "/routes/web.php";
$routes = "
// Auto-generated CRUD routes for {$table}
\$router->map('GET', '/{$table}', 'App\\Controllers\\{$controllerName}@index');
\$router->map('GET', '/{$table}/[i:id]', 'App\\Controllers\\{$controllerName}@show');
\$router->map('POST', '/{$table}', 'App\\Controllers\\{$controllerName}@store');
\$router->map('PUT', '/{$table}/[i:id]', 'App\\Controllers\\{$controllerName}@update');
\$router->map('DELETE', '/{$table}/[i:id]', 'App\\Controllers\\{$controllerName}@destroy');
";

if (!file_exists($routePath) || strpos(file_get_contents($routePath), "/{$table}") === false) {
    file_put_contents($routePath, PHP_EOL . $routes, FILE_APPEND);
    out('SUCCESS', "Routes added to web.php", 'green');
} else {
    out('WARNING', "Routes for '{$table}' already exist in web.php. Skipped.", 'yellow');
}

out('INFO', 'Running swagger:build');
echo shell_exec("composer swagger:build");

out('SUCCESS', "CRUD generated successfully for table '{$table}'!", 'green');
