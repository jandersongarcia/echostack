<?php

require_once 'helper-script.php';

$table = $argv[1] ?? null;
if (!$table) {
    out('WARNING', "Please provide the table name: composer delete:crud table_name", 'yellow');
    exit;
}

$className = ucfirst(rtrim($table, 's'));
$controllerName = "{$className}Controller";
$serviceName = "{$className}Service";
$modelName = $className;

// File paths
$basePath = dirname(__DIR__, 2) . '/src';
$modelPath = "{$basePath}/Models/{$modelName}.php";
$servicePath = "{$basePath}/Services/{$serviceName}.php";
$controllerPath = "{$basePath}/Controllers/{$controllerName}.php";

// Delete PHP files if they exist
foreach ([$modelPath, $servicePath, $controllerPath] as $file) {
    if (file_exists($file)) {
        unlink($file);
        out('SUCCESS', "Deleted: {$file}", 'green');
    } else {
        out('WARNING', "Not found (skipped): {$file}", 'yellow');
    }
}

// === Remove related routes and auto-generated comments from routes/web.php, cleaning blank lines ===
$routeFile = dirname(__DIR__, 2) . '/routes/web.php';
if (file_exists($routeFile)) {
    $lines = file($routeFile);
    $newContent = '';
    $previousLineWasBlank = false;

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        // Skip routes or comments related to the table or controller
        if (
            str_contains($trimmedLine, "/v1/{$table}") ||
            str_contains($trimmedLine, $controllerName) ||
            str_contains($trimmedLine, "// Auto-generated CRUD routes for {$table}")
        ) {
            continue;
        }

        // Clean multiple consecutive blank lines
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
    out('SUCCESS', "Routes and comments for '{$table}' removed and blank lines cleaned from routes/web.php", 'green');
} else {
    out('ERROR', "routes/web.php not found.", 'red');
}

out('SUCCESS', "CRUD successfully deleted for table '{$table}'.", 'green');
out('INFO', 'Running swagger:build...');
echo shell_exec("composer swagger:build");
