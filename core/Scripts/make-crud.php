<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Medoo\Medoo;

// Carrega variáveis do .env
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
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

$table = $argv[1] ?? null;
if (!$table) {
    echo "Informe o nome da tabela: composer make:crud nome_da_tabela\n";
    exit;
}

// Captura colunas da tabela
$columns = $database->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
if (!$columns) {
    echo "Tabela '$table' não encontrada.\n";
    exit;
}

// Prepara nomes das classes
$className = ucfirst(rtrim($table, 's'));
$controllerName = "{$className}Controller";
$serviceName = "{$className}Service";
$modelName = $className;

// Cria diretórios se não existirem
$basePath = dirname(__DIR__, 1) . '/../src';
@mkdir("{$basePath}/Models", 0775, true);
@mkdir("{$basePath}/Services", 0775, true);
@mkdir("{$basePath}/Controllers", 0775, true);

// Caminhos dos arquivos
$modelPath = "{$basePath}/Models/{$modelName}.php";
$servicePath = "{$basePath}/Services/{$serviceName}.php";
$controllerPath = "{$basePath}/Controllers/{$controllerName}.php";

// Verifica se arquivos já existem
if (file_exists($modelPath) || file_exists($servicePath) || file_exists($controllerPath)) {
    echo "⚠️  O CRUD para '{$table}' já existe. Use 'composer delete:crud {$table}' antes de gerar novamente.\n";
    exit;
}

// Gera Model
file_put_contents($modelPath, "<?php

namespace Src\Models;

class {$modelName}
{
" . implode("\n", array_map(fn($col) => "    public \${$col['Field']};", $columns)) . "

    // Personalize métodos e lógica de transformação conforme necessário.
}
");

// Gera Service com lógica básica
file_put_contents($servicePath, "<?php

namespace Src\Services;

use Medoo\\Medoo;

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

// Gera Controller com estrutura REST básica
file_put_contents($controllerPath, "<?php

namespace Src\Controllers;

use Src\Services\\{$serviceName};

class {$controllerName}
{
    protected \$service;

    public function __construct()
    {
        \$this->service = new {$serviceName}();
    }

    public function index()
    {
        echo json_encode(\$this->service->list());
    }

    public function show(\$id)
    {
        echo json_encode(\$this->service->get(\$id));
    }

    public function store()
    {
        \$data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['created' => \$this->service->create(\$data)]);
    }

    public function update(\$id)
    {
        \$data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['updated' => \$this->service->update(\$id, \$data)]);
    }

    public function destroy(\$id)
    {
        echo json_encode(['deleted' => \$this->service->delete(\$id)]);
    }
}
");

// Adiciona rotas ao arquivo routes/web.php
$rotaPath = dirname(__DIR__, 2) . "/routes/web.php";
$rotas = "
// Rotas automáticas para o CRUD de {$table}
\$router->map('GET', '/v1/{$table}', 'Src\\\\Controllers\\\\{$controllerName}#index');
\$router->map('GET', '/v1/{$table}/[i:id]', 'Src\\\\Controllers\\\\{$controllerName}#show');
\$router->map('POST', '/v1/{$table}', 'Src\\\\Controllers\\\\{$controllerName}#store');
\$router->map('PUT', '/v1/{$table}/[i:id]', 'Src\\\\Controllers\\\\{$controllerName}#update');
\$router->map('DELETE', '/v1/{$table}/[i:id]', 'Src\\\\Controllers\\\\{$controllerName}#destroy');
";

// Checa se as rotas já existem
$rotasExistem = file_exists($rotaPath) && strpos(file_get_contents($rotaPath), "/v1/{$table}") !== false;

if (!$rotasExistem) {
    file_put_contents($rotaPath, PHP_EOL . $rotas, FILE_APPEND);
    echo "Rotas adicionadas ao arquivo web.php\n";
} else {
    echo "⚠️  Rotas para '{$table}' já existem no arquivo web.php. Nada foi adicionado.\n";
}

echo "✅ CRUD gerado com sucesso para a tabela '{$table}'!\n";
