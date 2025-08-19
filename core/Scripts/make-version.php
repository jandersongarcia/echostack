<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;

// Caminhos
$basePath = PathResolver::basePath();
$srcPath = $basePath . '/app';

// Carrega .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->load();

// Define idioma padrão
$lang = LanguageHelper::getDefaultLanguage();

// Carrega traduções
$langFile = "$basePath/core/Lang/{$lang}.php";
if (!file_exists($langFile)) {
    echo "⚠️  Language '$lang' not found. Using 'us'.\n";
    $langFile = "$basePath/core/Lang/us.php";
}
$langData = include $langFile;

// Função de tradução
function t(array $lang, string $key, array $replace = []): string {
    $msg = $lang['make:version'][$key] ?? $key;
    foreach ($replace as $search => $value) {
        $msg = str_replace(':' . $search, $value, $msg);
    }
    return $msg;
}

// Função para erro em vermelho
function error(string $message): void {
    echo "\033[31m$message\033[0m" . PHP_EOL;
}

// Verifica se o argumento da versão foi passado
$inputVersion = $argv[1] ?? null;

// Valida a versão passada
if ($inputVersion !== null && !ctype_digit($inputVersion)) {
    error(t($langData, 'invalid_version'));
    exit(1);
}

// Se não informado, calcula próxima versão com base nas pastas existentes
if ($inputVersion === null) {
    $versions = array_filter(glob($srcPath . '/V*'), 'is_dir');
    $nums = array_map(fn($dir) => (int) filter_var(basename($dir), FILTER_SANITIZE_NUMBER_INT), $versions);
    $version = $nums ? max($nums) + 1 : 1;
} else {
    $version = (int) $inputVersion;
    if (is_dir($srcPath . "/V{$version}")) {
        error(t($langData, 'already_exists', ['version' => "V{$version}"]));
        exit(1);
    }
}

$versionDir = $srcPath . "/V{$version}";
@mkdir($versionDir, 0775, true);

// Cria subpastas se não existirem
$subdirs = ['Controllers', 'Models', 'Services', 'Validators'];
foreach ($subdirs as $sub) {
    $subPath = "$versionDir/$sub";
    if (!is_dir($subPath)) {
        mkdir($subPath, 0775, true);
    }
}

// Cria o arquivo ApiInfo.php
$apiInfoContent = <<<PHP
<?php

namespace V{$version};

/**
 * @OA\Info(
 *     title="API V{$version}",
 *     version="{$version}.0.0",
 *     description="API Documentation Version {$version}"
 * )
 */
class ApiInfo {}
PHP;

file_put_contents("{$versionDir}/ApiInfo.php", $apiInfoContent);

echo t($langData, 'creating', ['version' => "V{$version}"]) . PHP_EOL;
echo t($langData, 'folders_created') . PHP_EOL;
echo "Arquivo ApiInfo.php criado em app/V{$version}/ApiInfo.php" . PHP_EOL;

// Cria o arquivo de rotas
$routesPath = "$basePath/routes";
$routeFile = "$routesPath/V{$version}.php";

if (!is_dir($routesPath)) {
    mkdir($routesPath, 0775, true);
}

if (!file_exists($routeFile)) {
    $routeContent = <<<PHP
<?php

use Core\\Routing\\Router;

Router::group('/V{$version}', function () {
    // Define your routes here
    // Example:
    // Router::get('/status', [\V{$version}\Controllers\StatusController::class, 'index']);
});
PHP;

    file_put_contents($routeFile, $routeContent);
    echo t($langData, 'route_file_created', ['version' => "V{$version}"]) . PHP_EOL;
}


// Atualiza o composer.json
$composerFile = $basePath . '/composer.json';
$composer = json_decode(file_get_contents($composerFile), true);

$namespace = "V{$version}\\";
$relativePath = "app/V{$version}/";

if (!isset($composer['autoload']['psr-4'][$namespace])) {
    $composer['autoload']['psr-4'][$namespace] = $relativePath;
    file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo t($langData, 'psr4_updated') . PHP_EOL;
}

// Atualiza autoload
exec('composer dump-autoload');
echo t($langData, 'autoload_updated') . PHP_EOL;

// Executa do swagger-build.php
exec("php " . escapeshellarg("{$basePath}/core/Scripts/swagger-build.php") . " V{$version}");
echo t($langData, 'swagger_updated', ['version' => "V{$version}"]) . PHP_EOL;

// Exibe a versão criada
echo t($langData, 'version_created', ['version' => "V{$version}"]) . PHP_EOL;
echo t($langData, 'success') . PHP_EOL;