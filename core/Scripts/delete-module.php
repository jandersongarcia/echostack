<?php
/**
 * Script: core/Scripts/delete-module.php
 * Uso: composer delete:module NomeDaEntidade v1
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;

$basePath = PathResolver::basePath();

// Idioma
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "$basePath/core/Lang/{$lang}.php";
if (!file_exists($langFile)) {
    $langFile = "$basePath/core/Lang/en.php";
}
$__ = include $langFile;
$t = fn($key, $replacements = []) =>
    str_replace(
        array_map(fn($k) => ":{$k}", array_keys($replacements)),
        array_values($replacements),
        $__['delete:module'][$key] ?? $key
    );

// Argumentos ajustados para compatibilidade com composer.json
$entity = $argv[2] ?? null;
$version = $argv[3] ?? null;

if (!$entity || !$version || !preg_match('/^v[0-9]+$/i', $version)) {
    echo $t('usage') . "\n";
    exit(1);
}

$entity = ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $entity));
$namespacePrefix = 'V' . ltrim(strtolower($version), 'v');
$routePath = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $entity));
$controllerNamespace = "App\\{$namespacePrefix}\\Controllers\\{$entity}Controller";

$deletedFiles = 0;
$notFoundFiles = 0;

$dirs = [
    "app/{$namespacePrefix}/Controllers" => "{$entity}Controller.php",
    "app/{$namespacePrefix}/Models"      => "{$entity}.php",
    "app/{$namespacePrefix}/Validators"  => "{$entity}Validator.php",
    "app/{$namespacePrefix}/Services"    => "{$entity}Service.php",
];

foreach ($dirs as $folder => $file) {
    $abs = PathResolver::basePath() . "/$folder/$file";
    if (file_exists($abs)) {
        try {
            unlink($abs);
            echo $t('file_deleted', ['file' => "$folder/$file"]) . "\n";
            $deletedFiles++;
        } catch (Throwable $e) {
            echo "\033[31m" . $t('file_failed', ['file' => "$folder/$file", 'error' => $e->getMessage()]) . "\033[0m\n";
        }
    } else {
        echo $t('file_not_found', ['file' => "$folder/$file"]) . "\n";
        $notFoundFiles++;
    }

    // Remove diretório se estiver vazio
    if (is_dir($base = PathResolver::basePath() . "/$folder") && count(glob("$base/*")) === 0) {
        @rmdir($base);
    }
}

$routeFile = PathResolver::basePath() . "/routes/{$namespacePrefix}.php";
if (!file_exists($routeFile)) {
    echo $t('routes_not_found', ['file' => "routes/{$namespacePrefix}.php"]) . "\n";
    exit(0);
}

$lines = file($routeFile);
$output = '';
$removed = 0;

foreach ($lines as $line) {
    $trimmed = trim($line);
    if (
        str_contains($trimmed, "/{$routePath}") &&
        str_contains($trimmed, $controllerNamespace)
    ) {
        $removed++;
        echo $t('route_removed', ['line' => $trimmed]) . "\n";
        continue;
    }
    $output .= $line;
}

file_put_contents($routeFile, $output);

if ($removed) {
    echo $t('routes_cleaned', ['count' => $removed]) . "\n";
} else {
    echo $t('no_routes_found', ['route' => "/{$routePath}"]) . "\n";
}

echo $t('summary', [
    'files' => $deletedFiles,
    'skipped' => $notFoundFiles,
    'routes' => $removed
]) . "\n";

exit(0);
