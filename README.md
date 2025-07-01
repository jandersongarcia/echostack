# EchoAPI – Lightweight PHP Microstack for REST APIs

EchoAPI is a minimalist microstack for developers who want to build REST APIs in PHP quickly, with excellent organization and low coupling.
It works like a backend toolbox — providing only the essentials for routing, database access, validation, authentication, caching, and logging.
Perfect for those who want to avoid heavy frameworks and focus on a functional, lightweight, and easy-to-maintain API.

It provides built-in support for:

* Routing with AltoRouter
* Lightweight ORM with Medoo
* Validation with Respect\Validation
* Logging with Monolog
* API Key Authentication
* JWT Authentication
* OAuth 2.0 Authentication (Google, Microsoft, GitHub, Facebook, LinkedIn)
* Flexible Caching with Symfony Cache (Filesystem, Redis, APCu)
* Optional Telegram Integration

---

## Technologies Used

* PHP 8.x
* Medoo (PDO wrapper)
* AltoRouter (routing)
* Monolog (logging)
* Respect\Validation (validation)
* Symfony Console (CLI scripts)
* Symfony Cache (multi-driver caching)
* vlucas/phpdotenv (environment variables)
* Firebase PHP-JWT (JWT Authentication)
* TheNetworg OAuth2 Azure (Microsoft OAuth)
* League OAuth2 Client (Google, GitHub, Facebook, LinkedIn)

---

## Directory Structure

```txt
project-root/
├── app/                
│   ├── api/            # Public entry point for the backend
│   └── docs/           # Generated OpenAPI documentation
├── bootstrap/          # Application initialization
├── config/             # Environment and provider configurations
│   └── oauth_providers.php   # OAuth providers config
├── core/               
│   ├── Helpers/        
│   ├── Migration/      
│   ├── OpenApi/        
│   ├── Scripts/        # CLI tools (make, delete, etc.)
│   ├── Services/       # Core services (Auth, Cache, OAuth)
│   ├── Utils/          
│   └── Dispatcher.php  
├── storage/     
│   ├── cache/          # Filesystem cache storage
│   └── logs/           # Application logs
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

# Edit the .env file with your database, cache, and OAuth settings

# Create cache and logs directories
mkdir -p storage/cache storage/logs
chmod -R 775 storage
```

---

## Running an Endpoint

Standard request flow:

1. The client sends a request (e.g., `GET /v1/health`)
2. `public/index.php` acts as the entry point
3. Middlewares (API Key, JWT Auth, etc.) are loaded
4. The route is resolved
5. The Controller returns JSON

### Test via terminal:

```bash
curl http://localhost:8080/v1/health
```

---

## API Key Authentication

EchoAPI offers a simple **API Key** authentication system to secure your endpoints without OAuth complexity.

### Generate a new API Key

```bash
composer generate:apikey
```

> When you run this command, EchoAPI generates a random key and writes it to `.env`.

### Using the API Key

You can provide the API Key in **two ways**:

**Option 1 – Authorization header**

```http
Authorization: Bearer YOUR_API_KEY
```

**Option 2 – x-api-key header**

```http
x-api-key: YOUR_API_KEY
```

If the API Key is missing or invalid, the API returns **HTTP 401 Unauthorized**.

---

## JWT Authentication (Optional)

EchoAPI includes a lightweight JWT authentication system.

> **Important:**
> If `JWT_SECRET` is empty, all routes allow public access automatically.

### Generate the authentication system

```bash
composer make:auth
```

This creates Controllers, Services, Middleware (`AuthMiddleware`), and routes.

---

### Run database migrations

```bash
composer migration:auth
```

Creates the following tables:

* `users`
* `tokens`
* `password_resets`

---

### Default JWT Endpoints

| Method | Endpoint          | Function                  |
| ------ | ----------------- | ------------------------- |
| POST   | /v1/auth/login    | Login with email/password |
| POST   | /v1/auth/register | User registration         |
| POST   | /v1/auth/recover  | Request password reset    |
| POST   | /v1/auth/reset    | Reset password            |
| POST   | /v1/auth/logout   | Logout the user           |

---

## OAuth 2.0 Authentication

EchoAPI provides **first-class support for OAuth 2.0 providers** including:

* Google
* Microsoft Azure
* GitHub
* Facebook
* LinkedIn

You can generate all the configuration automatically:

### Generate OAuth configuration

```bash
composer make:oauth google github
```

This command:

✅ Installs the necessary Composer packages
✅ Creates or updates `config/oauth_providers.php`
✅ Creates `src/Services/OAuthService.php` if not present

> **Tip:** You can list multiple providers in the same command.

Example usage:

```bash
composer make:oauth google linkedin microsoft
```

### Remove OAuth configuration

```bash
composer delete:oauth github
```

If the `oauth_providers.php` becomes empty, EchoAPI will automatically delete both the config file and the `OAuthService` class.

---

### Example usage in your code

```php
$oauth = new \App\Services\OAuthService();
$provider = $oauth->getProvider('google');
```

From here you can:

* Generate the authorization URL:

  ```php
  $authUrl = $provider->getAuthorizationUrl();
  ```
* Exchange authorization code for token:

  ```php
  $token = $provider->getAccessToken('authorization_code', [
      'code' => $_GET['code']
  ]);
  ```
* Retrieve user details:

  ```php
  $user = $provider->getResourceOwner($token);
  ```

---

## Caching

EchoAPI includes a **unified CacheService** based on Symfony Cache, with support for:

* Filesystem (default)
* Redis (recommended for distributed environments)
* APCu (in-memory)

Caching is used transparently for:

* IP blocking after repeated authentication failures
* Rate limiting (if implemented)
* Application-level data caching

---

### Cache Configuration

In `.env`:

```ini
CACHE_DRIVER=filesystem

# Redis configuration (if used)
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

Creates:

```
app/docs/openapi.json
```

You can view it in Swagger Editor or Swagger UI.

---

## Telegram Integration

To receive error notifications in Telegram, configure `.env`:

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

## Available Scripts

| Command           | Description                                            |
| ----------------- | ------------------------------------------------------ |
| `make:module`     | Generate a module (Controller, Service, Model)         |
| `delete:module`   | Remove a module                                        |
| `make:crud`       | Generate CRUD from a table                             |
| `delete:crud`     | Remove CRUD files                                      |
| `list:crud`       | List existing CRUDs                                    |
| `make:auth`       | Generate JWT authentication system                     |
| `delete:auth`     | Remove authentication files                            |
| `migration:auth`  | Run database migrations for authentication             |
| `make:oauth`      | Generate OAuth configuration and install packages      |
| `delete:oauth`    | Remove OAuth configuration and clean up files if empty |
| `generate:apikey` | Create an API Key                                      |
| `log:test`        | Generate example logs                                  |
| `telegram:test`   | Send test message to Telegram                          |
| `swagger:build`   | Generate OpenAPI documentation                         |

---

## License

MIT
Developed by [Janderson Garcia](https://github.com/jandersongarcia)