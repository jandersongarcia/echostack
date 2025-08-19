#!/usr/bin/env php
<?php
/**
 * Script: delete-oauth.php
 * Purpose: Remove configured OAuth providers from EchoStack
 */

define('DIR', dirname(__DIR__, 2));
require_once DIR . '/vendor/autoload.php';
require_once 'helper-script.php';

$argv = array_unique($argv);
array_shift($argv);

if (empty($argv)) {
    out('ERROR', 'No provider specified. Example usage: php delete-oauth.php google github', 'red');
    exit(1);
}

// Paths
$configFile = DIR . '/config/oauth_providers.php';
$serviceFile = DIR . '/app/Services/OAuthService.php';
$controllerFile = DIR . '/app/Controllers/OAuthController.php';
$webRoutesFile = DIR . '/routes/web.php';
$publicRoutesFile = DIR . '/routes/public-routes.php';

if (!file_exists($configFile)) {
    out('ERROR', 'config/oauth_providers.php does not exist.', 'red');
    exit(1);
}

// Load config
$config = include $configFile;
$anyRemoved = false;

// Remove specified providers
foreach ($argv as $provider) {
    if (!isset($config[$provider])) {
        out('WARNING', "Provider '{$provider}' is not configured. Skipping.", 'yellow');
        continue;
    }
    unset($config[$provider]);
    $anyRemoved = true;
    out('SUCCESS', "Provider '{$provider}' removed.", 'green');
}

if (!$anyRemoved) {
    out('INFO', 'No providers were removed. Nothing else to do.', 'yellow');
    exit(0);
}

// Update oauth_providers.php
$configExport = empty($config) ? "[];" : var_export($config, true) . ";";
file_put_contents($configFile, "<?php\n\nreturn {$configExport}\n");
out('SUCCESS', 'config/oauth_providers.php updated.', 'green');

// If no providers remain, clean up auxiliary files
if (empty($config)) {
    out('INFO', 'No providers remain. Cleaning up auxiliary files (public-routes.php and oauth_providers.php will NOT be deleted).', 'yellow');

    if (file_exists($serviceFile)) {
        unlink($serviceFile);
        out('SUCCESS', 'OAuthService.php deleted.', 'green');
    }

    if (file_exists($controllerFile)) {
        unlink($controllerFile);
        out('SUCCESS', 'OAuthController.php deleted.', 'green');
    }

    // Clean public-routes.php (clear routes but don't delete file)
    if (file_exists($publicRoutesFile)) {
        $routes = include $publicRoutesFile;
        $routes = array_filter($routes, function ($route) {
            return !in_array($route, [
                '/oauth/[a:provider]/redirect',
                '/oauth/[a:provider]/callback'
            ]);
        });

        $content = "<?php\n\nreturn [\n";
        foreach ($routes as $route) {
            $content .= "    '" . addslashes($route) . "',\n";
        }
        $content .= "];\n";

        file_put_contents($publicRoutesFile, $content);
        out('SUCCESS', 'OAuth routes removed from public-routes.php. File kept.', 'green');
    }

    // Clean web.php
    if (file_exists($webRoutesFile)) {
        $lines = file($webRoutesFile, FILE_IGNORE_NEW_LINES);
        $newLines = [];
        $skip = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '// OAuth routes') {
                $skip = true;
                continue;
            }

            if ($skip) {
                if (strpos($trimmed, '$router->map') !== false) {
                    continue;
                } else {
                    $skip = false;
                }
            }

            $newLines[] = $line;
        }

        $contentClean = preg_replace(
            "/(\r?\n){3,}/",
            "\n\n",
            implode(PHP_EOL, $newLines)
        );

        file_put_contents($webRoutesFile, $contentClean);
        out('SUCCESS', 'OAuth routes removed from web.php.', 'green');
    }
}

// Composer advice
out('INFO', 'If you no longer need the provider packages, run composer remove manually:', 'yellow');
foreach ($argv as $provider) {
    switch ($provider) {
        case 'google':
            out('INFO', 'composer remove league/oauth2-google');
            break;
        case 'azure':
            out('INFO', 'composer remove thenetworg/oauth2-azure');
            break;
        case 'facebook':
            out('INFO', 'composer remove league/oauth2-facebook');
            break;
        case 'github':
            out('INFO', 'composer remove league/oauth2-github');
            break;
        case 'linkedin':
            out('INFO', 'composer remove league/oauth2-linkedin');
            break;
    }
}

// Rebuild Swagger docs
out('INFO', 'Running Swagger build...');
echo shell_exec("composer swagger:build");

out('SUCCESS', 'OAuth cleanup completed.', 'green');
