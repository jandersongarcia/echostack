<?php

// Ajuste de diretório base

define('DIR', str_replace('scripts', '', __DIR__));
require DIR . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCrudCommand extends Command
{
    public function __construct()
    {
        parent::__construct('generate:crud');
    }

    protected function configure()
    {
        $this
            ->setDescription('Generates a CRUD structure for an entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = ucfirst($input->getArgument('entity'));
        $plural = strtolower($entity) . 's';

        $baseDir = DIR;
        $controllerDir = $baseDir . 'src/Controllers';
        $modelDir = $baseDir . 'src/Models';
        $validatorDir = $baseDir . 'src/Validators';
        $routeFile = $baseDir . 'routes/web.php';

        @mkdir($controllerDir, 0777, true);
        @mkdir($modelDir, 0777, true);
        @mkdir($validatorDir, 0777, true);

        // Controller
        $controllerFile = "$controllerDir/{$entity}Controller.php";
        if (!file_exists($controllerFile)) {
            $controllerTemplate = "<?php\n\nnamespace App\\Controllers;\n\nuse App\\Validators\\{$entity}Validator;\n\nclass {$entity}Controller\n{\n    public function index() { echo json_encode(['message' => 'Listing {$plural}']); }\n    public function show(\$id) { echo json_encode(['message' => 'Details of {$entity} with ID ' . \$id]); }\n    public function store() { \$data = json_decode(file_get_contents('php://input'), true); (new {$entity}Validator())->validate(\$data); echo json_encode(['message' => '{$entity} successfully created']); }\n    public function update(\$id) { \$data = json_decode(file_get_contents('php://input'), true); (new {$entity}Validator())->validate(\$data); echo json_encode(['message' => '{$entity} successfully updated']); }\n    public function delete(\$id) { echo json_encode(['message' => '{$entity} with ID ' . \$id . ' deleted']); }\n}";
            file_put_contents($controllerFile, $controllerTemplate);
            $output->writeln("Controller created: {$controllerFile}");
        } else {
            $output->writeln("Controller already exists: {$controllerFile}");
        }

        // Model
        $modelFile = "$modelDir/{$entity}.php";
        if (!file_exists($modelFile)) {
            $modelTemplate = "<?php\n\nnamespace App\\Models;\n\nclass {$entity}\n{\n    // Define attributes and database interactions here\n}";
            file_put_contents($modelFile, $modelTemplate);
            $output->writeln("Model created: {$modelFile}");
        } else {
            $output->writeln("Model already exists: {$modelFile}");
        }

        // Validator
        $validatorFile = "$validatorDir/{$entity}Validator.php";
        if (!file_exists($validatorFile)) {
            $validatorTemplate = "<?php\n\nnamespace App\\Validators;\n\nuse Respect\\Validation\\Validator as v;\n\nclass {$entity}Validator\n{\n    public function validate(array \$data)\n    {\n        v::key('name', v::stringType()->length(1, 100))->assert(\$data);\n    }\n}";
            file_put_contents($validatorFile, $validatorTemplate);
            $output->writeln("Validator created: {$validatorFile}");
        } else {
            $output->writeln("Validator already exists: {$validatorFile}");
        }

        // Routes
        $routeContent = file_get_contents($routeFile);
        $useStatement = "use App\\Controllers\\{$entity}Controller;";

        if (strpos($routeContent, $useStatement) === false) {
            $routeContent = preg_replace("/(<\?php\n)/", "$1{$useStatement}\n", $routeContent, 1);
            file_put_contents($routeFile, $routeContent);
            $output->writeln("Use statement added to routes file.");
        } else {
            $output->writeln("Use statement for {$entity}Controller already exists.");
        }

        $routeContent = file_get_contents($routeFile);
        if (strpos($routeContent, "/{$plural}") === false) {
            $routeEntry = "\n\n// Routes for {$entity}\n";
            $routeEntry .= "$router->map('GET', '/{$plural}', '{$entity}Controller#index');\n";
            $routeEntry .= "$router->map('GET', '/{$plural}/[i:id]', '{$entity}Controller#show');\n";
            $routeEntry .= "$router->map('POST', '/{$plural}', '{$entity}Controller#store');\n";
            $routeEntry .= "$router->map('PUT', '/{$plural}/[i:id]', '{$entity}Controller#update');\n";
            $routeEntry .= "$router->map('DELETE', '/{$plural}/[i:id]', '{$entity}Controller#delete');\n";

            file_put_contents($routeFile, $routeEntry, FILE_APPEND);
            $output->writeln("Routes added to routes file.");
        } else {
            $output->writeln("Routes for {$entity} already exist in web.php.");
        }

        return Command::SUCCESS;
    }
}

$application = new Application();
$application->add(new GenerateCrudCommand());
$application->run();
