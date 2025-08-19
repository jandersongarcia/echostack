<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;

// Setup
$basePath = PathResolver::basePath();
$srcPath = "$basePath/app";
$routesPath = "$basePath/routes";
$swaggerPath = "$basePath/core/OpenApi/Versions";
$trashBase = "$basePath/storage/trash";
$composerFile = "$basePath/composer.json";
$deprecatedFile = "$swaggerPath/deprecated.json";

// Load .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->load();

// Load lang
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "$basePath/core/lang/{$lang}.php";
$lang = file_exists($langFile) ? include $langFile : include "$basePath/core/lang/us.php";

// Translation helper
function t(array $lang, string $key, array $replace = []): string {
    $msg = $lang['delete:version'][$key] ?? $key;
    foreach ($replace as $search => $value) {
        $msg = str_replace(':' . $search, $value, $msg);
    }
    return $msg;
}

// Move recursivamente
function moveRecursive(string $source, string $dest): bool {
    if (!file_exists($source)) return false;
    if (is_file($source)) return rename($source, $dest);

    mkdir($dest, 0775, true);
    foreach (scandir($source) as $item) {
        if ($item === '.' || $item === '..') continue;
        $srcItem = "$source/$item";
        $dstItem = "$dest/$item";
        moveRecursive($srcItem, $dstItem);
    }
    return rmdir($source);
}

// Argumento
$inputVersion = $argv[1] ?? null;
if (!$inputVersion || !ctype_digit($inputVersion)) {
    echo "\033[31m" . t($lang, 'invalid') . "\033[0m" . PHP_EOL;
    exit(1);
}

$version = "V{$inputVersion}";
$srcDir = "$srcPath/{$version}";
$routeFile = "$routesPath/{$version}.php";
$swaggerFile = "$swaggerPath/openapi-{$version}.json";

// Verifica existência
if (!is_dir($srcDir)) {
    echo "\033[31m" . t($lang, 'not_found', ['version' => $version]) . "\033[0m" . PHP_EOL;
    exit(1);
}

// Gera diretório de descarte
$uuid = uniqid();
$trashDir = "$trashBase/{$version}_{$uuid}";
mkdir($trashDir, 0775, true);

// Move app/Vx
if (is_dir($srcDir)) {
    moveRecursive($srcDir, "$trashDir/app/$version");
    echo t($lang, 'deleted', ['version' => $version]) . PHP_EOL;
}

// Move rota
if (file_exists($routeFile)) {
    mkdir("$trashDir/routes", 0775, true);
    rename($routeFile, "$trashDir/routes/{$version}.php");
    echo t($lang, 'route_removed', ['version' => $inputVersion]) . PHP_EOL;
}

// Move Swagger JSON
if (file_exists($swaggerFile)) {
    mkdir("$trashDir/swagger", 0775, true);
    rename($swaggerFile, "$trashDir/swagger/openapi-{$version}.json");
    echo t($lang, 'swagger_removed', ['version' => $inputVersion]) . PHP_EOL;
}

// Remove namespace do composer.json
$composer = json_decode(file_get_contents($composerFile), true);
$namespace = "{$version}\\";
if (isset($composer['autoload']['psr-4'][$namespace])) {
    unset($composer['autoload']['psr-4'][$namespace]);
    file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo t($lang, 'composer_updated') . PHP_EOL;
}

// Dump autoload
exec('composer dump-autoload');
echo t($lang, 'autoload_dumped') . PHP_EOL;

// Remove de deprecated.json
if (file_exists($deprecatedFile)) {
    $deprecated = json_decode(file_get_contents($deprecatedFile), true);
    $deprecated['deprecated_versions'] = array_filter(
        $deprecated['deprecated_versions'] ?? [],
        fn($v) => strtolower($v) !== strtolower("v{$inputVersion}")
    );
    file_put_contents($deprecatedFile, json_encode($deprecated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo t($lang, 'removed_from_deprecated', ['version' => $version]) . PHP_EOL;
}

// Registra meta
$meta = [
    'version' => $version,
    'deleted_at' => date('c'),
    'uuid' => $uuid
];
file_put_contents("$trashDir/meta.json", json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
