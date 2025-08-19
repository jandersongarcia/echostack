<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;

// Caminhos
$basePath = PathResolver::basePath();
$srcPath = $basePath . '/app';
$deprecatedFile = $basePath . '/core/OpenApi/Versions/deprecated.json';

// Carrega .env
$dotenv = Dotenv::createImmutable($basePath);
$dotenv->load();

// Define idioma padrão
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "$basePath/core/Lang/" . strtolower($lang) . ".php";
$langData = file_exists($langFile) ? require $langFile : require "$basePath/core/Lang/us.php";

// Função de tradução
function t(array $lang, string $key, array $replace = []): string {
    $msg = $lang['deprecated:version'][$key] ?? $key;
    foreach ($replace as $search => $value) {
        $msg = str_replace(':' . $search, $value, $msg);
    }
    return $msg;
}

// Função para erro em vermelho
function error(string $message): void {
    echo "\033[31m$message\033[0m" . PHP_EOL;
}

// Função para sucesso em verde
function success(string $message): void {
    echo "\033[32m$message\033[0m" . PHP_EOL;
}

// Argumento da versão
$versionArg = $argv[1] ?? null;
if (!$versionArg || !ctype_digit($versionArg)) {
    error(t($langData, 'invalid'));
    exit(1);
}

$version = "V{$versionArg}";
$versionDir = $srcPath . "/$version";

if (!is_dir($versionDir)) {
    error(t($langData, 'not_found', ['version' => $version]));
    exit(1);
}

// Carrega ou cria o JSON de deprecated
$deprecated = file_exists($deprecatedFile)
    ? json_decode(file_get_contents($deprecatedFile), true)
    : ['deprecated_versions' => []];

// Verifica duplicidade
if (in_array(strtolower($version), array_map('strtolower', $deprecated['deprecated_versions']))) {
    echo "[!] " . t($langData, 'already_deprecated', ['version' => $version]) . PHP_EOL;
    exit(0);
}

// Adiciona versão ao array
$deprecated['deprecated_versions'][] = strtolower($version);
file_put_contents($deprecatedFile, json_encode($deprecated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Mensagem final
success("[✔] " . t($langData, 'marked', ['version' => $version]));
