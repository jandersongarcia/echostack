<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Core\Helpers\PathResolver;

class DeleteModuleCommand extends Command
{
    public function __construct()
    {
        parent::__construct('delete:module');
    }

    protected function configure()
    {
        $this
            ->setDescription('Remove todos os arquivos e rotas gerados para um módulo')
            ->addArgument('entity', InputArgument::REQUIRED, 'O nome da entidade (ex: User, Product, Order)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entity = ucfirst($input->getArgument('entity'));
        $plural = strtolower($entity) . 's';

        $controllerFile = PathResolver::srcPath("Controllers/{$entity}Controller.php");
        $modelFile = PathResolver::srcPath("Models/{$entity}.php");
        $validatorFile = PathResolver::srcPath("Validators/{$entity}Validator.php");
        $serviceFile = PathResolver::srcPath("Services/{$entity}Service.php");
        $routeFile = PathResolver::routesPath("web.php");
        $backupRouteFile = $routeFile . '.bak';

        // Deletar arquivos
        foreach ([$controllerFile, $modelFile, $validatorFile, $serviceFile] as $file) {
            if (file_exists($file)) {
                unlink($file);
                $output->writeln("✅ Arquivo removido: {$file}");
            } else {
                $output->writeln("⚠ Arquivo não encontrado: {$file}");
            }
        }

        // Backup de segurança das rotas
        if (file_exists($routeFile)) {
            copy($routeFile, $backupRouteFile);
            $output->writeln("💄 Backup de rotas criado em: {$backupRouteFile}");
        }

        // Limpar rotas
        $routeContent = file_get_contents($routeFile);

        // Remove o use statement
        $useStatement = "use App\\Controllers\\{$entity}Controller;\n";
        $routeContent = str_replace($useStatement, '', $routeContent);

        // Remove o bloco de rotas
        $pattern = "/\\/\\/ Rotas para {$entity}.*?(?=\\n\\n|\Z)/s";
        $routeContent = preg_replace($pattern, '', $routeContent);

        file_put_contents($routeFile, $routeContent);
        $output->writeln("✅ Rotas removidas do arquivo web.php");

        return Command::SUCCESS;
    }
}

$application = new Application();
$application->add(new DeleteModuleCommand());
$application->run();