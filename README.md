# EchoAPI – Lightweight PHP Microstack for REST APIs

EchoAPI is a minimalist microstack for developers who want to build REST APIs in PHP quickly, with excellent organization and low coupling.
It works like a backend toolbox — providing only the essentials for routing, database access, validation, authentication, email delivery, caching, and logging.
Perfect for anyone who wants to avoid heavy frameworks and focus on a functional, lightweight, and easy-to-maintain API.

It includes built-in support for:

✅ Routing with AltoRouter
✅ Lightweight ORM with Medoo
✅ Validation with Respect\Validation
✅ Logging with Monolog
✅ API Key Authentication
✅ JWT Authentication
✅ OAuth 2.0 Authentication (Google, Microsoft Azure, LinkedIn)
✅ Flexible Caching with Symfony Cache (Filesystem, Redis, APCu)
✅ Email delivery via PHPMailer
✅ Optional Telegram integration

---

## Technologies Used

* PHP 8.x
* Medoo (PDO wrapper)
* AltoRouter (routing)
* Monolog (logging)
* Respect\Validation (validation)
* Symfony Console (CLI scripts)
* Symfony Cache (multi-driver caching)
* Predis (Redis client)
* PHPMailer (SMTP email)
* Firebase PHP-JWT (JWT authentication)
* TheNetworg OAuth2 Azure
* League OAuth2 Client (Google, LinkedIn)
* vlucas/phpdotenv (environment variables)

---

## Directory Structure

```txt
project-root/
├── app/
│   └── docs/            # Generated OpenAPI documentation
├── bootstrap/           # Application initialization
├── config/
│   ├── oauth_providers.php   # OAuth providers configuration
│   └── php_mailer.php        # SMTP settings for PHPMailer
├── core/
│   ├── Helpers/
│   ├── Migration/
│   ├── OpenApi/
│   ├── Scripts/         # CLI tools (make, delete, etc.)
│   ├── Services/        # Core services (Auth, Cache, OAuth)
│   ├── Utils/           # Utilities (MailHelper, etc.)
│   └── Dispatcher.php
├── storage/
│   ├── cache/           # Filesystem cache storage
│   └── logs/            # Application logs
├── middleware/
├── routes/
├── src/
│   ├── Controllers/
│   ├── Docs/
│   ├── Models/
│   ├── Services/
│   ├── Utils/
│   ├── Validators/
│   └── Views/
│       └── emails/
├── .env
├── composer.json
└── README.md
```

---

## Installation

```bash
# Clone the repository
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI

# Install dependencies
composer install

# Copy the environment file
cp .env_root .env

# Edit the .env file with your database, cache, and Telegram settings

# Create cache and logs directories
mkdir -p storage/cache storage/logs
chmod -R 775 storage
```

---

## Running an Endpoint

Standard request flow:

1. The client sends a request (e.g., `GET /v1/health`)
2. `public/index.php` acts as the entry point
3. Middlewares are loaded (API Key, JWT Auth, etc.)
4. The route is resolved
5. The Controller returns JSON

### Quick Test

```bash
curl http://localhost:8080/v1/health
```

---

## API Key Authentication

EchoAPI provides a simple **API Key authentication** system to protect your endpoints without requiring OAuth complexity.

### Generate a New API Key

```bash
composer generate:key
```

This command generates a random key and writes it to `.env` (`API_KEY`).

### How to Use the API Key

You can send the API key in two ways:

**Authorization header:**

```http
Authorization: Bearer YOUR_API_KEY
```

**or**

**x-api-key header:**

```http
x-api-key: YOUR_API_KEY
```

If the API key is missing or invalid, the API responds with **HTTP 401 Unauthorized**.

---

## JWT Authentication

EchoAPI includes a lightweight JWT authentication system.

> **Note:** If `JWT_SECRET` is empty in `.env`, all routes will allow public access by default.

### Generate the Authentication System

```bash
composer make:auth
```

This command generates:

* Controllers
* Services
* Middleware (`AuthMiddleware`)
* Routes

---

### Default JWT Endpoints

| Method | Endpoint          | Purpose                   |
| ------ | ----------------- | ------------------------- |
| POST   | /v1/auth/login    | Login with email/password |
| POST   | /v1/auth/register | User registration         |
| POST   | /v1/auth/recover  | Request password reset    |
| POST   | /v1/auth/reset    | Reset password            |
| POST   | /v1/auth/logout   | Log out the user          |

---

## OAuth 2.0 – Plug & Play Authentication

EchoAPI offers **first-class support for OAuth 2.0 providers**, including:

* Facebook
* Github
* Google
* Microsoft Azure
* LinkedIn

---

### Generate OAuth Configuration

```bash
composer make:oauth google linkedin azure
```

This command will:

* Install all required packages
* Create or update `config/oauth_providers.php`
* Generate `src/Services/OAuthService.php`

---

### Default OAuth Endpoints

Below are the default endpoints automatically registered for each provider:

| Method | Endpoint                | Purpose                                |
| ------ | ----------------------- | -------------------------------------- |
| POST   | `/v1/facebook/redirect` | Redirect user to Facebook login        |
| POST   | `/v1/facebook/callback` | Handle Facebook callback               |
| POST   | `/v1/github/redirect`   | Redirect user to GitHub login          |
| POST   | `/v1/github/callback`   | Handle GitHub callback                 |
| POST   | `/v1/google/redirect`   | Redirect user to Google login          |
| POST   | `/v1/google/callback`   | Handle Google callback                 |
| POST   | `/v1/linkedin/redirect` | Redirect user to LinkedIn login        |
| POST   | `/v1/linkedin/callback` | Handle LinkedIn callback               |
| POST   | `/v1/azure/redirect`    | Redirect user to Microsoft Azure login |
| POST   | `/v1/azure/callback`    | Handle Microsoft Azure callback        |

> **Note:** The `:provider` segment refers to the OAuth provider slug (e.g., `google`, `linkedin`, `azure`).

---

### Important

Before using OAuth, you **must configure** the credentials in the file:

```
config/oauth_providers.php
```

Example configuration for Azure:

```php
<?php

return [
    'azure' => [
        'class' => '\\TheNetworg\\OAuth2\\Client\\Provider\\Azure',
        'env' => [
            'clientId'     => 'YOUR_AZURE_CLIENT_ID',
            'clientSecret' => 'YOUR_AZURE_CLIENT_SECRET',
            'redirectUri'  => 'YOUR_AZURE_REDIRECT_URI',
            'tenant'       => 'YOUR_AZURE_TENANT_ID',
        ],
    ],
];
```

✅ **Make sure to replace** all placeholder values (`YOUR_AZURE_CLIENT_ID`, etc.) with your actual credentials.

---

### Removing a Provider

To remove an OAuth provider configuration:

```bash
composer delete:oauth linkedin
```

---

### Example Usage

```php
// Initialize the OAuth service
$oauth = new \App\Services\OAuthService();

// Load the Google provider
$provider = $oauth->getProvider('google');

// Generate the authorization URL to redirect the user
$authUrl = $provider->getAuthorizationUrl();

// Exchange the authorization code for an access token
$token = $provider->getAccessToken('authorization_code', [
    'code' => $_GET['code']
]);

// Retrieve the authenticated user details
$user = $provider->getResourceOwner($token);
```

---

## Email Delivery

EchoAPI provides **native email support** via PHPMailer.

**SMTP settings** are stored in `config/mail.php`.

### Example Usage

```php
use Core\Utils\MailHelper;

$mail = new MailHelper();
$mail->send(
    'recipient@example.com',
    'Subject here',
    '<p>Hello, this is a test email.</p>'
);
```

---

## Caching

EchoAPI includes a **unified CacheService** powered by Symfony Cache, supporting:

* Filesystem (default)
* Redis
* APCu

Configure in `.env`:

```ini
CACHE_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

If Redis or APCu is unavailable, Filesystem fallback is automatic.

---

## Swagger Documentation

Generate the OpenAPI specification:

```bash
composer swagger:build
```

This will create:

```
app/docs/openapi.json
```

---

## Telegram Integration

To receive error notifications on Telegram, configure `.env`:

```ini
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

Test the integration:

```bash
composer telegram:test
```

---

## Available Composer Scripts

| Command         | Description                                    |
| --------------- | ---------------------------------------------- |
| `make:module`   | Generate a module (Controller, Service, Model) |
| `delete:module` | Remove a module                                |
| `make:crud`     | Generate CRUD based on a database table        |
| `delete:crud`   | Remove generated CRUD files                    |
| `list:crud`     | List all existing CRUDs                        |
| `make:auth`     | Generate JWT authentication system             |
| `delete:auth`   | Remove authentication files                    |
| `make:oauth`    | Generate OAuth configuration                   |
| `delete:oauth`  | Remove OAuth configuration                     |
| `generate:key`  | Generate a new API Key                         |
| `log:test`      | Generate sample log entries                    |
| `telegram:test` | Send a test message to Telegram                |
| `swagger:build` | Generate OpenAPI documentation                 |

---

## License

MIT License
Developed by [Janderson Garcia](https://github.com/jandersongarcia)
