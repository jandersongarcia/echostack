<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once 'helper-script.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$langCode = strtolower($_ENV['LANGUAGE'] ?? 'en');
$langFile = dirname(__DIR__, 2) . "/core/lang/{$langCode}.php";
$lang = file_exists($langFile) ? require $langFile : [];
$msg = $lang['delete:crud'] ?? [];

$table = $argv[1] ?? null;
$versionInput = $argv[2] ?? null;

if (!$table || !$versionInput) {
    out('WARNING', $msg['usage'] ?? "Uso incorreto.", 'yellow');
    exit;
}

$version = ucfirst(strtolower(trim($versionInput))); // ex: V1
$className = ucfirst(rtrim($table, 's'));
$controllerName = "{$className}Controller";
$serviceName = "{$className}Service";
$modelName = $className;

$basePath = dirname(__DIR__, 2) . "/app/{$version}";
$modelPath = "$basePath/Models/{$modelName}.php";
$servicePath = "$basePath/Services/{$serviceName}.php";
$controllerPath = "$basePath/Controllers/{$controllerName}.php";

foreach ([$modelPath, $servicePath, $controllerPath] as $file) {
    if (file_exists($file)) {
        unlink($file);
        out('SUCCESS', str_replace(':file', $file, $msg['deleted'] ?? "Deleted: :file"), 'green');
    } else {
        out('WARNING', str_replace(':file', $file, $msg['skipped'] ?? "Not found: :file"), 'yellow');
    }
}

$routeFile = dirname(__DIR__, 2) . '/routes/web.php';
if (file_exists($routeFile)) {
    $lines = file($routeFile);
    $newContent = '';
    $previousLineWasBlank = false;

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if (
            str_contains($trimmedLine, "/{$version}/{$table}") ||
            str_contains($trimmedLine, $controllerName) ||
            str_contains($trimmedLine, "// Auto-generated CRUD routes for {$table}")
        ) {
            continue;
        }

        if ($trimmedLine === '') {
            if (!$previousLineWasBlank) {
                $newContent .= PHP_EOL;
                $previousLineWasBlank = true;
            }
        } else {
            $newContent .= $line;
            $previousLineWasBlank = false;
        }
    }

    file_put_contents($routeFile, trim($newContent) . PHP_EOL);
    out('SUCCESS', str_replace(':table', $table, $msg['routes_cleaned'] ?? 'Rotas limpas.'), 'green');
} else {
    out('ERROR', $msg['routes_file_missing'] ?? 'Arquivo de rotas não encontrado.', 'red');
}

out('SUCCESS', str_replace(':table', $table, $msg['crud_deleted'] ?? 'CRUD removido.'), 'green');
out('INFO', $msg['swagger_running'] ?? 'Atualizando Swagger...', 'cyan');
echo shell_exec("composer swagger:build");
