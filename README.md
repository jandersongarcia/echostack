
<p align="center">
  <img src="app/assets/logo-echoapi.png" alt="EchoAPI logo" width="200"/>
</p>

# EchoAPI – Lightweight PHP Microstack for REST APIs

**EchoAPI** is a minimalist microstack designed for developers who want to build RESTful APIs in PHP with speed, clean structure, and low coupling.  
Rather than being a full-stack framework, EchoAPI acts as a backend toolbox — delivering only the essential components needed for routing, validation, authentication, caching, logging, and external integrations.

Ideal for developers seeking a functional, lightweight, and maintainable API architecture without the overhead of complex frameworks.

---

## ✅ Key Features

* Routing with **AltoRouter**
* Lightweight ORM using **Medoo**
* Data validation with **Respect\Validation**
* Structured logging via **Monolog**
* Multiple authentication layers:
  * API Key
  * JWT (JSON Web Tokens)
  * OAuth 2.0 (Google, Microsoft, LinkedIn, etc.)
* Flexible caching via **Symfony Cache** (Filesystem, Redis, APCu)
* Native email support with **PHPMailer**
* Real-time error alerts through **Telegram**

---

## 🛠️ Technologies Used

* **PHP 8.x**
* Medoo (PDO wrapper)
* AltoRouter (Routing)
* Monolog (Logging)
* Respect\Validation (Validation)
* Symfony Console (CLI scripts)
* Symfony Cache (Multi-driver caching)
* Predis (Redis integration)
* PHPMailer (SMTP email)
* Firebase PHP-JWT (JWT support)
* TheNetworg OAuth2 Azure (Azure AD)
* League OAuth2 Client (Google, LinkedIn)
* vlucas/phpdotenv (Environment config)

---

## 📁 Project Structure

```txt
project-root/
├── app/
│   └── docs/            # Generated Swagger/OpenAPI documentation
├── bootstrap/           # Application bootstrap
├── config/
│   ├── oauth_providers.php   # OAuth credentials
│   └── php_mailer.php        # PHPMailer SMTP settings
├── core/
│   ├── Helpers/
│   ├── Migration/
│   ├── OpenApi/
│   ├── Scripts/         # CLI tools
│   ├── Services/        # Core services (Auth, Cache, OAuth, etc.)
│   ├── Utils/           # Shared utilities
│   └── Dispatcher.php
├── storage/
│   ├── cache/           # Cache files
│   └── logs/            # Rotated logs
├── middleware/          # Custom middleware
├── routes/              # Route definitions
├── src/                 # Application source code
│   ├── Controllers/
│   ├── Docs/
│   ├── Models/
│   ├── Services/
│   ├── Utils/
│   ├── Validators/
│   └── Views/emails/    # Email templates
├── .env                 # Environment variables
├── composer.json
└── README.md
````

---

## 📦 Running with Docker

EchoAPI includes a ready-to-use Docker setup to simplify development and ensure consistency across environments.

### Requirements

* [Docker Desktop](https://www.docker.com/products/docker-desktop)
* `docker compose` installed (usually included with Docker)

### 1. Build and Start the Containers

```bash
docker compose up --build -d
```

Access the API at: [http://localhost:8080](http://localhost:8080)

> The container runs PHP 8.3 + Apache, MySQL 8, and Redis.

### 2. Install Dependencies Inside the Container

```bash
docker compose exec app composer install
```

### 3. Create Storage Folders (if not present)

```bash
mkdir -p storage/cache storage/logs
chmod -R 775 storage
```

### 4. Configure Your `.env` File

Make sure your `.env` matches the container environment:

```ini
DB_HOST=db
DB_PORT=3306
DB_NAME=echoapi
DB_USER=root
DB_PASS=root

CACHE_DRIVER=redis
REDIS_HOST=redis
```

Test the health endpoint:
[http://localhost:8080/v1/health](http://localhost:8080/v1/health)

---

## 🚀 Manual Installation

```bash
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI
composer install
cp .env_root .env
mkdir -p storage/cache storage/logs
chmod -R 775 storage
```

Edit `.env` with your database, cache, and Telegram settings.

---

## 🔄 Request Flow

1. Request hits `index.php` (entry point)
2. Middlewares are applied (Auth, CORS, etc.)
3. Routing is resolved via AltoRouter
4. Controller is executed
5. JSON response is returned

---

## 🔐 Authentication

### API Key

* Add your key to `.env` → `API_KEY=...`
* Send via header:

  * `Authorization: Bearer ...` or
  * `x-api-key: ...`
* Returns `401` if missing or invalid

Generate:

```bash
composer generate:key
```

---

### JWT (JSON Web Token)

Generate system:

```bash
composer make:auth
```

Default endpoints:

| Method | Endpoint          | Purpose          |
| ------ | ----------------- | ---------------- |
| POST   | /v1/auth/login    | Login user       |
| POST   | /v1/auth/register | Create new user  |
| POST   | /v1/auth/recover  | Request password |
| POST   | /v1/auth/reset    | Reset password   |
| POST   | /v1/auth/logout   | Logout user      |

---

### OAuth 2.0 Providers

Generate config:

```bash
composer make:oauth google linkedin azure
```

Supported providers: Google, LinkedIn, Microsoft Azure, Facebook, GitHub

Configure credentials in `config/oauth_providers.php`

Remove:

```bash
composer delete:oauth linkedin
```

---

## 📬 Email Support

* Uses **PHPMailer**
* SMTP settings: `config/php_mailer.php`

Send using:

```php
$mail = new MailHelper();
$mail->send('to@example.com', 'Subject', '<p>Body</p>');
```

---

## 🧠 Caching

Supports **Symfony Cache** with:

* Filesystem (default)
* Redis
* APCu

Configure in `.env`:

```ini
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
```

Fallback to filesystem if Redis/APCu is unavailable.

---

## 📃 Swagger Documentation

Generate:

```bash
composer swagger:build
```

Output: `app/docs/openapi.json`

Use with tools like [Swagger UI](https://editor.swagger.io/)

---

## 📢 Telegram Error Alerts

Enable in `.env`:

```ini
TELEGRAM_BOT_TOKEN=xxx
TELEGRAM_CHAT_ID=xxx
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

Test:

```bash
composer telegram:test
```

Alerts include level, message, IP, endpoint, and exception details.

---

## 📚 Logging System

* Based on **Monolog**
* Logs saved to `/storage/logs/`
* Files:

  * `app.log` for general events (`INFO+`)
  * `error.log` for errors only (`ERROR+`)
* Logs are **rotated daily** (configurable via `LOG_RETENTION_DAYS`)
* Telegram alerts are sent on critical levels with full context

Includes:

* IP address
* User agent
* URI
* UID
* Exception trace (when available)

Test:

```bash
composer log:test
```

---

## ⚙️ Composer Scripts

| Command         | Description                         |
| --------------- | ----------------------------------- |
| `make:module`   | Create controller + service + model |
| `delete:module` | Remove a module                     |
| `make:crud`     | Generate CRUD from a database table |
| `delete:crud`   | Delete CRUD components              |
| `list:crud`     | Show registered CRUDs               |
| `make:auth`     | Generate JWT auth system            |
| `delete:auth`   | Remove JWT auth files               |
| `make:oauth`    | Setup OAuth provider(s)             |
| `delete:oauth`  | Remove provider config              |
| `generate:key`  | Generate a new API Key              |
| `log:test`      | Trigger sample logs                 |
| `telegram:test` | Send test alert to Telegram         |
| `swagger:build` | Generate OpenAPI documentation      |

---

## 🧾 License

**MIT License**
Developed by [Janderson Garcia](https://github.com/jandersongarcia)

```