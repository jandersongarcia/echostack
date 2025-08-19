<?php

/**
 * Script: diagnostics.php
 * Uso: composer diagnostics
 */

$vendorAutoload = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$vendorAutoload || !file_exists($vendorAutoload)) {
    echo "❌ vendor/autoload.php não encontrado. Execute 'composer install'.\n";
    exit(1);
}
require_once $vendorAutoload;

use Core\Helpers\PathResolver;
use Core\Utils\Core\LanguageHelper;

// 1. Base path
$basePath = PathResolver::basePath();

// 2. Carrega idioma
$lang = LanguageHelper::getDefaultLanguage();
$langFile = "{$basePath}/core/Lang/{$lang}.php";
if (!file_exists($langFile)) {
    $lang = 'en';
    $langFile = "{$basePath}/core/Lang/en.php";
}
$__ = include $langFile;
$t = fn($key, $replacements = []) =>
    str_replace(
        array_map(fn($k) => ":{$k}", array_keys($replacements)),
        array_values($replacements),
        $__['diagnostics'][$key] ?? $key
    );

// 3. Verifica .env
$envPath = "{$basePath}/.env";
if (!file_exists($envPath)) {
    echo $t('env_missing') . "\n";
    exit(1);
}

$envContent = file_get_contents($envPath);
if ($envContent === false) {
    echo $t('env_read_error') . "\n";
    exit(1);
}

// 4. Alternar ou adicionar ENABLE_DIAGNOSTICS
$envLines = explode("\n", $envContent);
$found = false;

foreach ($envLines as &$line) {
    if (preg_match('/^\s*ENABLE_DIAGNOSTICS\s*=\s*(true|false)\s*$/i', $line, $matches)) {
        $found = true;
        $current = strtolower(trim($matches[1])) === 'true';
        $new = $current ? 'false' : 'true';
        $line = "ENABLE_DIAGNOSTICS={$new}";
        echo $t($new === 'true' ? 'enabled' : 'disabled') . "\n";
        break;
    }
}
unset($line);

if (!$found) {
    $envLines[] = "ENABLE_DIAGNOSTICS=true";
    echo $t('created') . "\n";
}

file_put_contents($envPath, implode("\n", $envLines));
