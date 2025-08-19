<?php
/**
 * Script: core/Scripts/make-module.php
 * Uso: composer make:module Entity V1
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Core\Helpers\PathResolver;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Core\Utils\Core\LanguageHelper;

class MakeModuleCommand extends Command
{
    protected array $__;
    protected $translator;

    public function __construct()
    {
        parent::__construct('make:module');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Gera automaticamente o módulo completo para uma entidade em uma versão específica')
            ->addArgument('entity', InputArgument::REQUIRED, 'O nome da entidade (ex: User, Product, Order)')
            ->addArgument('version', InputArgument::REQUIRED, 'A versão da API (ex: v1, v2)');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $basePath = PathResolver::basePath();
        $lang = LanguageHelper::getDefaultLanguage();
        $langFile = "$basePath/core/Lang/{$lang}.php";
        if (!file_exists($langFile)) {
            $langFile = "$basePath/core/Lang/en.php";
        }
        $this->__ = include $langFile;
        $this->translator = fn($key, $replacements = []) =>
            str_replace(
                array_map(fn($k) => ":{$k}", array_keys($replacements)),
                array_values($replacements),
                $this->__['make:module'][$key] ?? $key
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $t = $this->translator;

        $rawEntity = ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $input->getArgument('entity')));
        $version = strtolower($input->getArgument('version'));
        $namespacePrefix = 'V' . ltrim($version, 'v');
        $routePath = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $rawEntity));

        $baseDir = PathResolver::basePath() . "/app/{$namespacePrefix}";
        $dirs = [
            'Controllers' => "$baseDir/Controllers",
            'Models' => "$baseDir/Models",
            'Validators' => "$baseDir/Validators",
            'Services' => "$baseDir/Services",
        ];

        foreach ($dirs as $dir) {
            @mkdir($dir, 0777, true);
        }

        $templates = [
            'Controllers' => [
                'path' => "{$dirs['Controllers']}/{$rawEntity}Controller.php",
                'content' => "<?php\n\nnamespace App\\{$namespacePrefix}\\Controllers;\n\nuse App\\{$namespacePrefix}\\Validators\\{$rawEntity}Validator;\nuse App\\{$namespacePrefix}\\Services\\{$rawEntity}Service;\n\nclass {$rawEntity}Controller\n{\n    public function index() { echo json_encode(['list' => '{$routePath}']); }\n    public function show(".'$id'.") { echo json_encode(['id' => \$id]); }\n    public function store() {\n        \$data = json_decode(file_get_contents('php://input'), true) ?? [];\n        (new {$rawEntity}Validator())->validate(\$data);\n        (new {$rawEntity}Service())->store(\$data);\n        echo json_encode(['created' => true]);\n    }\n    public function update(".'$id'.") {\n        \$data = json_decode(file_get_contents('php://input'), true) ?? [];\n        (new {$rawEntity}Validator())->validate(\$data);\n        (new {$rawEntity}Service())->update((int)\$id, \$data);\n        echo json_encode(['updated' => true]);\n    }\n    public function delete(".'$id'.") {\n        (new {$rawEntity}Service())->delete((int)\$id);\n        echo json_encode(['deleted' => true]);\n    }\n}\n"
            ],
            'Models' => [
                'path' => "{$dirs['Models']}/{$rawEntity}.php",
                'content' => "<?php\n\nnamespace App\\{$namespacePrefix}\\Models;\n\nclass {$rawEntity}\n{\n}\n"
            ],
            'Validators' => [
                'path' => "{$dirs['Validators']}/{$rawEntity}Validator.php",
                'content' => "<?php\n\nnamespace App\\{$namespacePrefix}\\Validators;\n\nuse Respect\\Validation\\Validator as v;\n\nclass {$rawEntity}Validator\n{\n    public function validate(array \$data): void\n    {\n        // TODO: regras de validação\n    }\n}\n"
            ],
            'Services' => [
                'path' => "{$dirs['Services']}/{$rawEntity}Service.php",
                'content' => "<?php\n\nnamespace App\\{$namespacePrefix}\\Services;\n\nclass {$rawEntity}Service\n{\n    public function store(array \$data): void {}\n    public function update(int \$id, array \$data): void {}\n    public function delete(int \$id): void {}\n}\n"
            ],
        ];

        foreach ($templates as $type => $tpl) {
            $label = $type . (file_exists($tpl['path']) ? '_exists' : '_created');
            $output->writeln($t($label, ['file' => $tpl['path']]));
            if (!file_exists($tpl['path'])) {
                file_put_contents($tpl['path'], $tpl['content']);
            }
        }

        $routeFile = PathResolver::basePath() . "/routes/{$namespacePrefix}.php";

        if (!file_exists($routeFile)) {
            file_put_contents($routeFile, "<?php\n\nuse Core\\Routing\\Router;\n\nRouter::group('/{$namespacePrefix}', function () {\n    global \$router;\n\n});\n");
            $output->writeln($t('route_file_created', ['file' => $routeFile]));
        }

        $content = file_get_contents($routeFile);
        $routeBlock = "\n    // Rotas para {$rawEntity}\n" .
            "    Router::map('GET', '/{$routePath}', 'App\\{$namespacePrefix}\\Controllers\\{$rawEntity}Controller@index');\n" .
            "    Router::map('GET', '/{$routePath}/[i:id]', 'App\\{$namespacePrefix}\\Controllers\\{$rawEntity}Controller@show');\n" .
            "    Router::map('POST', '/{$routePath}', 'App\\{$namespacePrefix}\\Controllers\\{$rawEntity}Controller@store');\n" .
            "    Router::map('PUT', '/{$routePath}/[i:id]', 'App\\{$namespacePrefix}\\Controllers\\{$rawEntity}Controller@update');\n" .
            "    Router::map('DELETE', '/{$routePath}/[i:id]', 'App\\{$namespacePrefix}\\Controllers\\{$rawEntity}Controller@delete');\n";

    if (!str_contains($content, $t('route_to')." {$rawEntity}")) {
            $groupStart = strpos($content, "Router::group(");
            $insertPos = strrpos($content, "});");
            if ($groupStart !== false && $insertPos !== false) {
                $content = substr_replace($content, $routeBlock, $insertPos, 0);
                file_put_contents($routeFile, $content);
                $output->writeln($t('routes_added', ['file' => $routeFile]));
            } else {
                $output->writeln($t('route_not_found'));
            }
        } else {
            $output->writeln($t('routes_already_exists', ['entity' => $rawEntity]));
        }

        return Command::SUCCESS;
    }
}

$application = new Application();
$application->add(new MakeModuleCommand());
$application->run();
