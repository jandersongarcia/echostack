#!/usr/bin/env php
<?php
/**
 * Script: make-oauth.php
 * Purpose: Automated setup of OAuth providers in EchoStack
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
array_shift($argv); // Remove script name
if (empty($argv)) {
    out('ERROR', 'No provider specified. Example usage: php make-oauth.php google github', 'red');
    exit(1);
}

// Paths
$configFile = DIR . '/config/oauth_providers.php';
$serviceFile = DIR . '/app/Services/OAuthService.php';
$controllerFile = DIR . '/app/Controllers/OAuthController.php';
$webRoutesFile = DIR . '/routes/web.php';
$publicRoutesFile = DIR . '/routes/public-routes.php';

// Load existing config
$config = [];
if (file_exists($configFile)) {
    $config = include $configFile;
    out('INFO', 'Existing oauth_providers.php loaded.');
}

// Install packages and update config
foreach ($argv as $provider) {
    if (!isset($supportedProviders[$provider])) {
        out('ERROR', "Provider '{$provider}' is not supported.", 'red');
        exit(1);
    }

    // Check if already configured
    if (isset($config[$provider])) {
        out('WARNING', "Provider '{$provider}' is already configured. Skipping installation.", 'yellow');
        continue;
    }

    $data = $supportedProviders[$provider];

    out('INFO', "Installing package {$data['package']}...");
    $resultCode = 0;
    passthru("composer require {$data['package']}", $resultCode);
    if ($resultCode !== 0) {
        out('ERROR', "Composer package installation failed for {$provider}.", 'red');
        exit(1);
    }

    $config[$provider] = [
        'class' => $data['class'],
        'env' => $data['env']
    ];
}

// Save config
$configExport = var_export($config, true);
if (!is_dir(dirname($configFile))) {
    mkdir(dirname($configFile), 0775, true);
}
file_put_contents($configFile, "<?php\n\nreturn {$configExport};\n");
out('SUCCESS', 'config/oauth_providers.php updated successfully.', 'green');

// Create OAuthService
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
    out('SUCCESS', 'OAuthService created.', 'green');
} else {
    out('INFO', 'OAuthService already exists. No changes made.');
}

// Create OAuthController
if (!file_exists($controllerFile)) {
    out('INFO', 'Creating OAuthController...');
    if (!is_dir(dirname($controllerFile))) {
        mkdir(dirname($controllerFile), 0775, true);
    }
    file_put_contents($controllerFile, <<<PHP
<?php
namespace App\Controllers;

use App\Services\OAuthService;

class OAuthController
{
    /**
     * @OA\Get(
     *   path="/oauth/{provider}/redirect",
     *   summary="Redirect to OAuth provider",
     *   @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     required=true,
     *     description="Provider name"
     *   ),
     *   @OA\Response(response=302, description="Redirecting")
     * )
     */
    public static function redirect(\$params)
    {
        \$providerName = \$params['provider'];
        \$oauth = new OAuthService();
        \$provider = \$oauth->getProvider(\$providerName);
        \$authUrl = \$provider->getAuthorizationUrl();
        \$_SESSION['oauth2state'] = \$provider->getState();
        header('Location: ' . \$authUrl);
        exit;
    }

    /**
     * @OA\Get(
     *   path="/oauth/{provider}/callback",
     *   summary="OAuth callback handler",
     *   @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     required=true,
     *     description="Provider name"
     *   ),
     *   @OA\Response(response=200, description="Success")
     * )
     */
    public static function callback(\$params)
    {
        \$providerName = \$params['provider'];
        \$oauth = new OAuthService();
        \$provider = \$oauth->getProvider(\$providerName);

        if (empty(\$_GET['state']) || \$_GET['state'] !== \$_SESSION['oauth2state']) {
            exit('Invalid OAuth state.');
        }

        \$token = \$provider->getAccessToken('authorization_code', [
            'code' => \$_GET['code'],
        ]);

        \$user = \$provider->getResourceOwner(\$token);

        echo '<pre>';
        echo "Access Token:\\n";
        var_dump(\$token->getToken());
        echo "\\nUser Details:\\n";
        var_dump(\$user->toArray());
        echo "\\nTODO: Link or create your local user here.";
        echo '</pre>';
    }
}
PHP
);
    out('SUCCESS', 'OAuthController created.', 'green');
} else {
    out('INFO', 'OAuthController already exists. No changes made.');
}

// Add routes to web.php
$routeFile = DIR . '/routes/web.php';
$existingRoutes = file_get_contents($routeFile);

$routesToAdd = [
    "// OAuth routes",
    "\$router->map('GET', '/oauth/[a:provider]/redirect', 'App\\\\Controllers\\\\OAuthController@redirect');",
    "\$router->map('GET', '/oauth/[a:provider]/callback', 'App\\\\Controllers\\\\OAuthController@callback');",
];

$newRoutes = "";

// Check for each line
foreach ($routesToAdd as $route) {
    if (strpos($existingRoutes, $route) === false) {
        $newRoutes .= PHP_EOL . $route;
    } else {
        out('WARNING', "Route already exists and was skipped: {$route}", 'yellow');
    }
}

// Append only if something new
if (!empty(trim($newRoutes))) {
    $contentTrimmed = rtrim($existingRoutes) . PHP_EOL . PHP_EOL . trim($newRoutes) . PHP_EOL;
    $contentClean = preg_replace("/(\r?\n){3,}/", "\n\n", $contentTrimmed);

    file_put_contents($routeFile, $contentClean);
    out('SUCCESS', 'OAuth routes added to routes/web.php', 'green');
} else {
    out('INFO', 'No new routes were added. All OAuth routes already exist.');
}

// Create or update public-routes.php
if (!file_exists($publicRoutesFile)) {
    out('INFO', 'Creating public-routes.php...');
    file_put_contents($publicRoutesFile, "<?php\n\nreturn [\n    '/oauth/[a:provider]/redirect',\n    '/oauth/[a:provider]/callback'\n];\n");
    out('SUCCESS', 'public-routes.php created.', 'green');
} else {
    // Load existing routes
    $routes = include $publicRoutesFile;
    $existing = is_array($routes) ? $routes : [];

    // List of OAuth routes to ensure
    $oauthRoutes = [
        '/oauth/[a:provider]/redirect',
        '/oauth/[a:provider]/callback',
    ];

    // Track whether we need to update
    $newAdded = false;

    foreach ($oauthRoutes as $route) {
        if (!in_array($route, $existing, true)) {
            $existing[] = $route;
            $newAdded = true;
            out('INFO', "Added route to public-routes.php: {$route}");
        }
    }

    if ($newAdded) {
        // Save updated routes
        $content = "<?php\n\nreturn [\n";
        foreach ($existing as $route) {
            $content .= "    '" . addslashes($route) . "',\n";
        }
        $content .= "];\n";

        file_put_contents($publicRoutesFile, $content);
        out('SUCCESS', 'public-routes.php updated with new OAuth routes.', 'green');
    } else {
        out('INFO', 'All OAuth routes already exist in public-routes.php.');
    }
}


// Rebuild Swagger docs
out('INFO', 'Running Swagger build...');
echo shell_exec("composer swagger:build");

out('SUCCESS', 'OAuth setup completed successfully!', 'green');
