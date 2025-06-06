<?php

// Carga do autoload segura via diretório absoluto:
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Core\Helpers\PathResolver;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModuleCommand extends Command
{
    public function __construct()
    {
        parent::__construct('make:module');
    }

    protected function configure()
    {
        $this
            ->setDescription('Gera automaticamente o módulo completo para uma entidade (Controller, Model, Validator, Service e Rotas)')
            ->addArgument('entity', InputArgument::REQUIRED, 'O nome da entidade (ex: User, Product, Order)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = ucfirst($input->getArgument('entity'));
        $plural = strtolower($entity) . 's';

        $controllerDir = PathResolver::srcPath('Controllers');
        $modelDir = PathResolver::srcPath('Models');
        $validatorDir = PathResolver::srcPath('Validators');
        $serviceDir = PathResolver::srcPath('Services');
        $routeFile = PathResolver::routesPath('web.php');

        @mkdir($controllerDir, 0777, true);
        @mkdir($modelDir, 0777, true);
        @mkdir($validatorDir, 0777, true);
        @mkdir($serviceDir, 0777, true);

        // Controller
        $controllerFile = "$controllerDir/{$entity}Controller.php";
        if (!file_exists($controllerFile)) {
            $controllerTemplate = "<?php\n\nnamespace App\\Controllers;\n\nuse App\\Validators\\{$entity}Validator;\nuse App\\Services\\{$entity}Service;\n\nclass {$entity}Controller\n{\n    public function index() { echo json_encode(['list' => '{$plural}']); }\n    public function show(" . '$id' . ") { echo json_encode(['id' => \$id]); }\n    public function store() { \n        \$data = json_decode(file_get_contents('php://input'), true); \n        (new {$entity}Validator())->validate(\$data); \n        (new {$entity}Service())->store(\$data); \n        echo json_encode(['created' => true]); \n    }\n    public function update(" . '$id' . ") { \n        \$data = json_decode(file_get_contents('php://input'), true); \n        (new {$entity}Validator())->validate(\$data); \n        (new {$entity}Service())->update(\$id, \$data); \n        echo json_encode(['updated' => true]); \n    }\n    public function delete(" . '$id' . ") { \n        (new {$entity}Service())->delete(\$id); \n        echo json_encode(['deleted' => true]); \n    }\n}";
            file_put_contents($controllerFile, $controllerTemplate);
            $output->writeln("✅ Controller criado: {$controllerFile}");
        } else {
            $output->writeln("⚠ Controller já existe: {$controllerFile}");
        }

        // Model
        $modelFile = "$modelDir/{$entity}.php";
        if (!file_exists($modelFile)) {
            $modelTemplate = "<?php\n\nnamespace App\\Models;\n\nclass {$entity}\n{\n    // Defina aqui os atributos e interações com o banco\n}";
            file_put_contents($modelFile, $modelTemplate);
            $output->writeln("✅ Model criado: {$modelFile}");
        } else {
            $output->writeln("⚠ Model já existe: {$modelFile}");
        }

        // Validator
        $validatorFile = "$validatorDir/{$entity}Validator.php";
        if (!file_exists($validatorFile)) {
            $validatorTemplate = "<?php\n\nnamespace App\\Validators;\n\nuse Respect\\Validation\\Validator as v;\n\nclass {$entity}Validator\n{\n    public function validate(array \$data)\n    {\n        v::key('name', v::stringType()->length(1, 100))->assert(\$data);\n    }\n}";
            file_put_contents($validatorFile, $validatorTemplate);
            $output->writeln("✅ Validator criado: {$validatorFile}");
        } else {
            $output->writeln("⚠ Validator já existe: {$validatorFile}");
        }

        // Service
        $serviceFile = "$serviceDir/{$entity}Service.php";
        if (!file_exists($serviceFile)) {
            $serviceTemplate = "<?php\n\nnamespace App\\Services;\n\nclass {$entity}Service\n{\n    public function store(array \$data) { /* lógica de inserção */ }\n    public function update(int \$id, array \$data) { /* lógica de atualização */ }\n    public function delete(int \$id) { /* lógica de exclusão */ }\n}";
            file_put_contents($serviceFile, $serviceTemplate);
            $output->writeln("✅ Service criado: {$serviceFile}");
        } else {
            $output->writeln("⚠ Service já existe: {$serviceFile}");
        }

        // Rotas
        $routeContent = file_get_contents($routeFile);
        $useStatement = "use App\\Controllers\\{$entity}Controller;";

        if (strpos($routeContent, $useStatement) === false) {
            $routeContent = preg_replace("/(<\?php\\n)/", "$1{$useStatement}\n", $routeContent, 1);
            file_put_contents($routeFile, $routeContent);
            $output->writeln("✅ Use statement adicionado nas rotas.");
        } else {
            $output->writeln("⚠ Use statement já existe nas rotas.");
        }

        $routeContent = file_get_contents($routeFile);
        if (strpos($routeContent, "/{$plural}") === false) {
            $routeEntry = "\n\n// Rotas para {$entity}\n";
            $routeEntry .= '$router->map' . "('GET', '/{$plural}', '{$entity}Controller#index');\n";
            $routeEntry .= '$router->map' . "('GET', '/{$plural}/[i:id]', '{$entity}Controller#show');\n";
            $routeEntry .= '$router->map' . "('POST', '/{$plural}', '{$entity}Controller#store');\n";
            $routeEntry .= '$router->map' . "('PUT', '/{$plural}/[i:id]', '{$entity}Controller#update');\n";
            $routeEntry .= '$router->map' . "('DELETE', '/{$plural}/[i:id]', '{$entity}Controller#delete');\n";

            file_put_contents($routeFile, $routeEntry, FILE_APPEND);
            $output->writeln("✅ Rotas adicionadas no arquivo web.php.");
        } else {
            $output->writeln("⚠ Rotas para {$entity} já existem.");
        }

        return Command::SUCCESS;
    }
}

$application = new Application();
$application->add(new MakeModuleCommand());
$application->run();
