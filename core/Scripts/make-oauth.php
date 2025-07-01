#!/usr/bin/env php
<?php
/**
 * Script: make-oauth.php
 * Purpose: Automated installation of OAuth providers in EchoAPI
 */

define('DIR', dirname(__DIR__, 2));
require_once DIR . '/vendor/autoload.php';
require_once 'helper-script.php';

// Supported providers
$supportedProviders = [
    'google' => [
        'package' => 'league/oauth2-google',
        'class' => '\\League\\OAuth2\\Client\\Provider\\Google',
        'env' => [
            'clientId'     => 'OAUTH_GOOGLE_CLIENT_ID',
            'clientSecret' => 'OAUTH_GOOGLE_CLIENT_SECRET',
            'redirectUri'  => 'OAUTH_GOOGLE_REDIRECT_URI'
        ]
    ],
    'azure' => [
        'package' => 'thenetworg/oauth2-azure',
        'class' => '\\TheNetworg\\OAuth2\\Client\\Provider\\Azure',
        'env' => [
            'clientId'     => 'OAUTH_MICROSOFT_CLIENT_ID',
            'clientSecret' => 'OAUTH_MICROSOFT_CLIENT_SECRET',
            'redirectUri'  => 'OAUTH_MICROSOFT_REDIRECT_URI',
            'tenant'       => 'OAUTH_MICROSOFT_TENANT_ID'
        ]
    ],
    'microsoft' => [
        'package' => 'thenetworg/oauth2-azure',
        'class' => '\\TheNetworg\\OAuth2\\Client\\Provider\\Azure',
        'env' => [
            'clientId'     => 'OAUTH_MICROSOFT_CLIENT_ID',
            'clientSecret' => 'OAUTH_MICROSOFT_CLIENT_SECRET',
            'redirectUri'  => 'OAUTH_MICROSOFT_REDIRECT_URI',
            'tenant'       => 'OAUTH_MICROSOFT_TENANT_ID'
        ]
    ],
    'facebook' => [
        'package' => 'league/oauth2-facebook',
        'class' => '\\League\\OAuth2\\Client\\Provider\\Facebook',
        'env' => [
            'clientId'     => 'OAUTH_FACEBOOK_CLIENT_ID',
            'clientSecret' => 'OAUTH_FACEBOOK_CLIENT_SECRET',
            'redirectUri'  => 'OAUTH_FACEBOOK_REDIRECT_URI'
        ]
    ],
    'github' => [
        'package' => 'league/oauth2-github',
        'class' => '\\League\\OAuth2\\Client\\Provider\\Github',
        'env' => [
            'clientId'     => 'OAUTH_GITHUB_CLIENT_ID',
            'clientSecret' => 'OAUTH_GITHUB_CLIENT_SECRET',
            'redirectUri'  => 'OAUTH_GITHUB_REDIRECT_URI'
        ]
    ],
    'linkedin' => [
        'package' => 'league/oauth2-linkedin',
        'class' => '\\League\\OAuth2\\Client\\Provider\\LinkedIn',
        'env' => [
            'clientId'     => 'OAUTH_LINKEDIN_CLIENT_ID',
            'clientSecret' => 'OAUTH_LINKEDIN_CLIENT_SECRET',
            'redirectUri'  => 'OAUTH_LINKEDIN_REDIRECT_URI'
        ]
    ],
];

$argv = array_unique($argv);

array_shift($argv); // Remove the script name
if (empty($argv)) {
    out('ERROR', 'No provider specified. Example usage: php make-oauth.php google github', 'red');
    exit(1);
}

// Paths
$configFile = DIR . '/config/oauth_providers.php';
$serviceFile = DIR . '/src/Services/OAuthService.php';

// Load existing config
$config = [];
if (file_exists($configFile)) {
    $config = include $configFile;
    out('INFO', 'Existing oauth_providers.php loaded.');
}

// Process each provider
foreach ($argv as $provider) {
    if (!isset($supportedProviders[$provider])) {
        out('ERROR', "Provider '{$provider}' is not supported.", 'red');
        exit(1);
    }
    $data = $supportedProviders[$provider];

    // Install the Composer package
    out('INFO', "Installing package {$data['package']}...");
    $resultCode = 0;
    passthru("composer require {$data['package']}", $resultCode);
    if ($resultCode !== 0) {
        out('ERROR', "Composer package installation failed for {$provider}.", 'red');
        exit(1);
    }

    // Update config
    $config[$provider] = [
        'class' => $data['class'],
        'env' => $data['env']
    ];
}

// Write config file
$configExport = var_export($config, true);
if (!is_dir(dirname($configFile))) {
    mkdir(dirname($configFile), 0775, true);
}
file_put_contents($configFile, "<?php\n\nreturn {$configExport};\n");
out('SUCCESS', 'config/oauth_providers.php updated successfully.', 'green');

// Create the Service if it does not exist
if (!file_exists($serviceFile)) {
    out('INFO', 'Creating OAuthService...');
    if (!is_dir(dirname($serviceFile))) {
        mkdir(dirname($serviceFile), 0775, true);
    }

    file_put_contents($serviceFile, <<<PHP
<?php
namespace App\Services;

class OAuthService
{
    public function getProvider(string \$providerKey)
    {
        \$config = include __DIR__ . '/../../config/oauth_providers.php';

        if (!isset(\$config[\$providerKey])) {
            throw new \\InvalidArgumentException("OAuth provider '\$providerKey' is not configured.");
        }

        \$providerConfig = \$config[\$providerKey];
        \$options = [];

        foreach (\$providerConfig['env'] as \$key => \$envVar) {
            \$value = getenv(\$envVar);
            if (!\$value) {
                throw new \\RuntimeException("Environment variable '\$envVar' is not defined.");
            }
            \$options[\$key] = \$value;
        }

        return new \$providerConfig['class'](\$options);
    }
}
PHP
);
    out('SUCCESS', 'OAuthService created at src/Services/OAuthService.php.', 'green');
} else {
    out('INFO', 'OAuthService already exists. No changes made.');
}

// Rebuild Swagger docs
out('INFO', 'Running Swagger build...');
echo shell_exec("composer swagger:build");

out('SUCCESS', 'OAuth setup completed successfully!', 'green');
