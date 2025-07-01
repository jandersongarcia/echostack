#!/usr/bin/env php
<?php
/**
 * Script: delete-oauth.php
 * Purpose: Remove configured OAuth providers from EchoAPI
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
$serviceFile = DIR . '/src/Services/OAuthService.php';

if (!file_exists($configFile)) {
    out('ERROR', 'config/oauth_providers.php does not exist.', 'red');
    exit(1);
}

// Load current config
$config = include $configFile;

// Remove specified providers
foreach ($argv as $provider) {
    if (!isset($config[$provider])) {
        out('ERROR', "Provider '{$provider}' is not configured in oauth_providers.php.", 'red');
        exit(1);
    }

    unset($config[$provider]);
    out('SUCCESS', "Provider '{$provider}' removed from configuration.", 'green');
}

// If no providers remain, delete both files
if (empty($config)) {
    if (file_exists($configFile)) {
        unlink($configFile);
        out('SUCCESS', 'config/oauth_providers.php deleted because it was empty.', 'green');
    }
    if (file_exists($serviceFile)) {
        unlink($serviceFile);
        out('SUCCESS', 'OAuthService.php deleted because no providers remain.', 'green');
    }
    out('SUCCESS', 'OAuth cleanup completed successfully.', 'green');
    exit(0);
}

// Otherwise, write updated config
$configExport = var_export($config, true);
file_put_contents($configFile, "<?php\n\nreturn {$configExport};\n");
out('SUCCESS', 'config/oauth_providers.php updated successfully.', 'green');

// Display composer advice
out('INFO', 'If you no longer need the provider packages, run composer remove manually:', 'yellow');
foreach ($argv as $provider) {
    switch ($provider) {
        case 'google':
            out('INFO', 'composer remove league/oauth2-google');
            break;
        case 'microsoft':
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
