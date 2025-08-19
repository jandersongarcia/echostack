<?php

use Core\Helpers\PathResolver;

// Verifica se a URI começa com /v0/{lang}
if (isset($_SERVER['REQUEST_URI']) && preg_match('#^/v0/([a-z]{2})#', $_SERVER['REQUEST_URI'], $matches)) {
    $langSlug = strtolower($matches[1]);
    $method = $_SERVER['REQUEST_METHOD'];

    // POST: Envio de formulário 

    if ($method === 'POST') {
        $envPath = PathResolver::basePath() . '/.env';

        // Garante existência do .env
        if (!file_exists($envPath)) {
            file_put_contents($envPath, '');
        }

        $envContent = file_get_contents($envPath);
        $post = $_POST;

        // Lista de chaves que podem ser atualizadas
        $allowedKeys = [
            'APP_URL',
            'DB_DRIVER',
            'DB_HOST',
            'DB_NAME',
            'DB_USER',
            'DB_PASS',
            'TIME_ZONE',
            'LANGUAGE'
        ];

        foreach ($allowedKeys as $key) {
            $value = trim($post[$key] ?? '');

            if ($value === '')
                continue;

            if (preg_match("/^{$key}=.*$/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= PHP_EOL . "{$key}={$value}";
            }
        }

        // Seta ECHO_INSTALLED como true
        if (preg_match("/^ECHO_INSTALLED=.*$/m", $envContent)) {
            $envContent = preg_replace("/^ECHO_INSTALLED=.*$/m", "ECHO_INSTALLED=true", $envContent);
        } else {
            $envContent .= PHP_EOL . "ECHO_INSTALLED=true";
        }

        // Limpa linhas em branco duplicadas
        $envContent = preg_replace("/(\R){3,}/", PHP_EOL . PHP_EOL, $envContent);
        $envContent = rtrim($envContent) . PHP_EOL;

        file_put_contents($envPath, $envContent);

        // Remove o instalador após configurar
        $installPath = PathResolver::basePath() . '/app/install.html';
        if (file_exists($installPath)) {
            unlink($installPath);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Environment file updated and installation completed.'
        ]);
        exit;
    }

    // GET: retorna status da instalação 
    // Carrega lista de idiomas disponíveis com base nos arquivos de idioma
    $langDir = PathResolver::basePath() . '/core/lang/';
    $langFiles = glob($langDir . '*.php');
    $availableLangs = [];

    foreach ($langFiles as $file) {
        $code = basename($file, '.php');
        $availableLangs[$code] = ucfirst($code);
    }

    // Se o idioma não estiver disponível, usar en como padrão
    $currentLang = array_key_exists($langSlug, $availableLangs) ? $langSlug : 'en';

    // Carrega o arquivo do idioma
    $langFile = $langDir . "{$currentLang}.php";
    $T = require $langFile;
    $T = $T['install'] ?? [];

    // Informações do sistema
    $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'curl', 'tokenizer', 'ctype', 'fileinfo'];
    $phpVersion = phpversion();
    $phpValid = version_compare($phpVersion, '8.1.0', '>=');

    $extensionsStatus = [];
    foreach ($requiredExtensions as $ext) {
        $extensionsStatus[$ext] = extension_loaded($ext);
    }

    // Variáveis do ambiente disponíveis
    $env = [
        'APP_URL' => $_ENV['APP_URL'] ?? null,
        'DB_DRIVER' => $_ENV['DB_DRIVER'] ?? 'mysql',
        'TIME_ZONE' => $_ENV['TIME_ZONE'] ?? 'UTC',
        'LANGUAGE' => $_ENV['LANGUAGE'] ?? $currentLang,
    ];

    $output = [
        'language' => $currentLang,
        'available_languages' => array_keys($availableLangs),
        'php_version' => $phpVersion,
        'php_valid' => $phpValid,
        'required_extensions' => $extensionsStatus,
        'env' => $env,
        'translation' => $T
    ];

    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}
