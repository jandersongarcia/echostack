<?php

use Core\Utils\Core\LanguageHelper;

$swaggerBase = $_ENV['SWAGGER_ROUTE'] ?? '/docs';
$swaggerDir = ROOT . '/core/OpenApi/Versions';

// Carrega idioma
$langCode = LanguageHelper::getDefaultLanguage();
$langFile = ROOT . "/core/lang/" . strtolower($langCode) . ".php";
$lang = file_exists($langFile) ? include $langFile : include ROOT . "/core/lang/us.php";

// Função de tradução
function t(array $lang, string $key, array $replace = []): string {
    $parts = explode('.', $key);
    $msg = $lang;
    foreach ($parts as $part) {
        $msg = $msg[$part] ?? $key;
    }
    foreach ($replace as $search => $value) {
        $msg = str_replace(':' . $search, $value, $msg);
    }
    return $msg;
}

// Rota: /v1/docs/swagger.json
$router->map('GET', '/[a:version]/docs/swagger.json', function ($version) use ($swaggerDir, $lang) {
    $env = $_ENV['APP_ENV'] ?? 'production';
    $enabled = filter_var($_ENV['SWAGGER_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $accessKey = $_ENV['SWAGGER_ACCESS_KEY'] ?? null;
    $providedKey = $_SERVER['HTTP_X_SWAGGER_KEY'] ?? '';

    if (!$enabled || $env === 'production') {
        http_response_code(403);
        echo json_encode(['error' => t($lang, 'swagger.disabled')]);
        return;
    }

    if ($accessKey && $providedKey !== $accessKey) {
        http_response_code(401);
        echo json_encode(['error' => t($lang, 'swagger.unauthorized')]);
        return;
    }

    if (!preg_match('#^v\d+$#i', $version)) {
        http_response_code(400);
        echo json_encode(['error' => t($lang, 'swagger.invalid_format')]);
        return;
    }

    $versionUpper = strtoupper($version);
    $swaggerPath = "{$swaggerDir}/openapi-{$versionUpper}.json";
    $deprecatedPath = "{$swaggerDir}/deprecated.json";

    if (!file_exists($swaggerPath)) {
        http_response_code(404);
        echo json_encode(['error' => t($lang, 'swagger.not_found', ['version' => $versionUpper])]);
        return;
    }

    $swagger = json_decode(file_get_contents($swaggerPath), true);

    // Verifica se a versão está descontinuada
    $deprecatedVersions = file_exists($deprecatedPath)
        ? json_decode(file_get_contents($deprecatedPath), true)['deprecated_versions'] ?? []
        : [];

    if (in_array(strtolower($version), array_map('strtolower', $deprecatedVersions))) {
        $swagger['info']['x-deprecated'] = true;
        $warning = t($lang, 'swagger.deprecated_warning');
        $swagger['info']['description'] = trim(($swagger['info']['description'] ?? '') . "\n\n" . $warning);
    }

    header('Content-Type: application/json');
    echo json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});
