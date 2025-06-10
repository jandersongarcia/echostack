<?php

$basePath = dirname(__DIR__, 2) . '/src';
$routePath = dirname(__DIR__, 2) . '/routes/web.php';

// Lista de entidades baseadas nos arquivos
$models = glob("{$basePath}/Models/*.php");
$services = glob("{$basePath}/Services/*Service.php");
$controllers = glob("{$basePath}/Controllers/*Controller.php");

function extractName($path, $suffix) {
    return basename($path, $suffix . '.php');
}

echo "📦 CRUDs Encontrados:\n";

$crudNames = [];
foreach ($models as $model) {
    $name = extractName($model, '');
    $crudNames[$name]['model'] = true;
}
foreach ($services as $service) {
    $name = extractName($service, 'Service');
    $crudNames[$name]['service'] = true;
}
foreach ($controllers as $controller) {
    $name = extractName($controller, 'Controller');
    $crudNames[$name]['controller'] = true;
}

foreach ($crudNames as $name => $parts) {
    echo "🔹 {$name}\n";
    echo "   - Model:      " . (!empty($parts['model']) ? '✅' : '❌') . "\n";
    echo "   - Service:    " . (!empty($parts['service']) ? '✅' : '❌') . "\n";
    echo "   - Controller: " . (!empty($parts['controller']) ? '✅' : '❌') . "\n";
}

echo "\n🔗 Rotas Detectadas:\n";

if (file_exists($routePath)) {
    $lines = file($routePath);
    foreach ($lines as $line) {
        // Limpa comentários e espaços
        $line = trim(preg_replace('/\/\/.*$/', '', $line));
        if (preg_match("/\\\$router->map\(\s*'([A-Z]+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*\);/", $line, $matches)) {
            list(, $method, $path, $target) = $matches;
            echo "🔹 {$method}\t{$path}  → {$target}\n";
        }
    }
} else {
    echo "⚠️  routes/web.php não encontrado.\n";
}

echo "\n✅ Fim da listagem.\n";
