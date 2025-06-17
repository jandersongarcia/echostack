<?php

require_once ('helper-script.php');

$table = $argv[1] ?? null;
if (!$table) {
    out('WARNING', "Informe o nome da tabela: composer delete:crud nome_da_tabela", 'yellow');
    exit;
}

$className = ucfirst(rtrim($table, 's'));
$controllerName = "{$className}Controller";
$serviceName = "{$className}Service";
$modelName = $className;

// Caminhos dos arquivos
$basePath = dirname(__DIR__, 2) . '/src';
$modelPath = "{$basePath}/Models/{$modelName}.php";
$servicePath = "{$basePath}/Services/{$serviceName}.php";
$controllerPath = "{$basePath}/Controllers/{$controllerName}.php";

// Apaga arquivos se existirem
foreach ([$modelPath, $servicePath, $controllerPath] as $file) {
    if (file_exists($file)) {
        unlink($file);
        out('SUCCESS', "Removido: {$file}", 'green');
    } else {
        out('WARNING', "Não encontrado (ignorando): {$file}", 'yellow');
    }
}

// Remove rotas do arquivo
$routeFile = dirname(__DIR__, 2) . '/routes/web.php';
if (file_exists($routeFile)) {
    $lines = file($routeFile);
    $filtered = array_filter($lines, function ($line) use ($table, $controllerName) {
        return !str_contains($line, "/v1/{$table}") && !str_contains($line, $controllerName);
    });

    file_put_contents($routeFile, implode('', $filtered));
    out('SUCCESS', "Rotas para '{$table}' removidas de routes/web.php", 'green');
} else {
    out('ERROR', "Arquivo de rotas não encontrado.", 'green');
}

out('SUCCESS', "CRUD removido com sucesso para a tabela '{$table}", 'green');
echo shell_exec("composer swagger:build");