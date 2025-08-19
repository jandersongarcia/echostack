<?php

$vendorAutoload = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$vendorAutoload || !file_exists($vendorAutoload)) {
    echo "❌ vendor/autoload.php não encontrado. Execute 'composer install'.\n";
    exit(1);
}
require_once $vendorAutoload;

use Core\Utils\Core\LanguageHelper;
use Core\Helpers\PathResolver;

// Caminhos principais
$root = PathResolver::basePath();
$versionsDir = "$root/app";
$outputDir = "$root/core/OpenApi/Versions";
$bootstrap = "$root/core/OpenApi/bootstrap-swagger.php";
$openapiBin = "$root/vendor/bin/openapi";

// Define versão alvo (ex: V1, V2...)
$args = array_slice($argv, 1);
$targetVersion = isset($args[0]) ? strtoupper($args[0]) : null;

// Define idioma padrão
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "$root/core/Lang/{$lang}.php";
if (!file_exists($langFile)) {
    echo "⚠️  Arquivo de idioma '{$lang}.php' não encontrado. Usando inglês como padrão.\n";
    $lang = 'en';
    $langFile = "$root/core/Lang/en.php";
}
$__ = include $langFile;

// Função de tradução
$t = fn($key, $replacements = []) =>
    str_replace(
        array_map(fn($k) => ":$k", array_keys($replacements)),
        array_values($replacements),
        $__["swagger:build"][$key] ?? $key
    );

// Função principal
function buildSwagger(
    string $version,
    string $srcPath,
    string $outputFile,
    string $bootstrap,
    string $openapiBin,
    callable $t
): void {
    echo $t('starting_one', ['version' => $version]) . "\n";

    if (!is_dir($srcPath)) {
        echo $t('not_found', ['version' => $version]) . "\n";
        return;
    }

    $cmd = escapeshellcmd("$openapiBin --bootstrap $bootstrap --output $outputFile $srcPath");
    exec($cmd . ' 2>&1', $output, $code);
    $rawOutput = implode("\n", $output);

    if (str_contains($rawOutput, '@OA\PathItem() not found')) {
        echo $t('no_paths_found', ['version' => $version]) . "\n";
    }

    if ($code === 0) {
        echo $t('success', ['file' => basename($outputFile)]) . "\n";
    } else {
        echo $t('error', ['version' => $version]) . "\n";
    }
}

// Filtra versões válidas, ignorando V0
function getVersionDirectories(string $path): array {
    return array_filter(
        scandir($path),
        fn($item) =>
            is_dir("$path/$item") &&
            preg_match('/^V\d+$/i', $item) &&
            strtoupper($item) !== 'V0'
    );
}

// Execução principal
if ($targetVersion) {
    $version = strtoupper($targetVersion);
    $srcPath = "$versionsDir/$version";
    $outputFile = "$outputDir/openapi-$version.json";
    buildSwagger($version, $srcPath, $outputFile, $bootstrap, $openapiBin, $t);
} else {
    echo $t('starting_all', ['src' => $versionsDir]) . "\n";
    foreach (getVersionDirectories($versionsDir) as $version) {
        $srcPath = "$versionsDir/$version";
        $outputFile = "$outputDir/openapi-$version.json";
        buildSwagger($version, $srcPath, $outputFile, $bootstrap, $openapiBin, $t);
    }
    echo $t('completed') . "\n";
}
