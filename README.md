<p align="center">
  <a href="https://github.com/jandersongarcia/EchoAPI">
    <img src="app/assets/logo-echoapi.png" alt="EchoAPI Logo" width="200" />
  </a>
</p>

# EchoAPI – Lightweight PHP Microstack for REST APIs

**EchoAPI** is a minimalist microstack designed for developers who want to build RESTful APIs in PHP with speed, clean structure, and low coupling.
Rather than being a full-stack framework, EchoAPI acts as a backend toolbox — delivering only the essential components needed for routing, validation, authentication, caching, logging, and external integrations.

Ideal for developers seeking a functional, lightweight, and maintainable API architecture without the overhead of complex frameworks.

---

## 📄 System Requirements

* PHP >= 8.1
* Composer >= 2.x
* MySQL 8+ or MariaDB
* Redis (optional, for caching)
* PHP Extensions:

  * pdo\_mysql
  * mbstring
  * openssl
  * curl
  * json

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

* PHP 8.x
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
├── app/                 # Swagger/OpenAPI docs
├── bootstrap/           # App bootstrap process
├── config/              # Configuration files
├── core/                # Kernel, helpers, services
├── storage/             # Cache & logs
├── middleware/          # HTTP middlewares
├── routes/              # Route definitions
├── src/                 # App logic (MVC)
├── .env                 # Environment settings
├── composer.json        # Dependencies & scripts
└── README.md
```

---

## 🗃 Database Initialization

EchoAPI comes with a default database structure and initial data available in:

```txt
core/Migration/auth-migrations.sql
```

This script creates the basic authentication tables: `users`, `roles`, `user_tokens`, and `password_resets`.

### Default user

* **Email:** `master@echoapi.local`
* **Password:** `master!123@`

> ⚠️ The password is hashed in the database. Use this user only for first login or local development.

### Automatic migration with Docker

If you're using Docker, set the following in your `.env`:

```ini
AUTO_MIGRATE=true
```

This will automatically import the `auth-migrations.sql` during container startup.

> 🔐 After successful migration, it is strongly recommended to set `AUTO_MIGRATE=false` to prevent re-imports and protect data integrity.

---

## 🚀 Manual Installation

```bash
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI
composer install
cp .env.example .env
chmod -R 775 storage
```

Configure your `.env` with DB, Redis, email, and Telegram.

---

## 🚧 Docker Support

EchoAPI supports Docker for rapid onboarding.

```bash
docker compose up --build -d
docker compose exec app composer install
```

Access: [http://localhost:8080](http://localhost:8080)

---

## 🚡 Request Lifecycle

1. Entry via `public/index.php`
2. Middlewares (API key, CORS, Auth)
3. Routes resolved via AltoRouter
4. Controller invoked
5. JSON response returned

---

## 🔐 Authentication Methods

### API Key

* Set `API_KEY=your_token` in `.env`
* Send in header: `Authorization: Bearer YOUR_KEY`

Generate:

```bash
composer generate:key
```

### JWT Auth

Generate system:

```bash
composer make:auth
```

Includes login, register, password reset, logout endpoints.

### OAuth 2.0

Supports Google, LinkedIn, Azure, Facebook, GitHub.

```bash
composer make:oauth google linkedin
```

---

## 📧 Email Support

Uses **PHPMailer** configured via `config/php_mailer.php`.

```php
$mail = new MailHelper();
$mail->send('to@example.com', 'Subject', '<p>Body</p>');
```

---

## 🔎 Caching

Configured via `.env`:

```ini
CACHE_DRIVER=redis
REDIS_HOST=redis
```

Fallbacks to filesystem if not available.

---

## 📃 Swagger Documentation

```bash
composer swagger:build
```

Output: `app/docs/openapi.json` (for Swagger UI or Redoc).

> ⚠️ When `APP_ENV=production`, access to `/v1/docs/swagger.json` is disabled for security reasons.

Access the interactive Swagger UI at the `/docs/` endpoint of your deployed application. For example:

```
http://localhost:8080/docs/     (local development)
```

---

## 💬 Telegram Notifications

Enable in `.env`:

```ini
TELEGRAM_BOT_TOKEN=xxx
TELEGRAM_CHAT_ID=xxx
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## 🗒 Logging

* Logs in `storage/logs/`
* `app.log`: info+
* `error.log`: error+
* Rotated daily

Test:

```bash
composer log:test
```

---

## ⚙️ Available Scripts

| Command        | Description                     |
| -------------- | ------------------------------- |
| make\:module   | Create controller/service/model |
| delete\:module | Remove a module                 |
| make\:crud     | CRUD generator                  |
| delete\:crud   | Delete CRUD set                 |
| list\:crud     | List registered CRUDs           |
| make\:auth     | JWT authentication scaffold     |
| delete\:auth   | Remove JWT files                |
| make\:oauth    | OAuth provider integration      |
| delete\:oauth  | Remove OAuth config             |
| generate\:key  | Generate API Key                |
| log\:test      | Generate sample logs            |
| telegram\:test | Test Telegram alert             |
| swagger\:build | Build OpenAPI spec              |

---

## 📜 Sample .env

```ini
APP_ENV=development
APP_DEBUG=true
API_KEY=your_api_key

DB_HOST=db
DB_PORT=3306
DB_NAME=echoapi
DB_USER=root
DB_PASS=root

CACHE_DRIVER=redis
REDIS_HOST=redis

TELEGRAM_BOT_TOKEN=xxx
TELEGRAM_CHAT_ID=xxx
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## ⚠️ Error Reference

| Code | Description                  | Details                                                          |
| ---- | ---------------------------- | ---------------------------------------------------------------- |
| E001 | `.env` not found             | The `.env` file is missing. Rename `.env.example` to `.env`.     |
| E002 | Missing environment variable | One or more required environment variables are missing or empty. |

### Example: E001 Response

```json
{
  "error": "Environment file not found",
  "message": "The \".env\" file is required. Please rename \".env.example\" to \".env\" and configure your environment variables.",
  "code": "E001"
}
```

### Example: E002 Response

```json
{
  "error": "Missing environment variable",
  "message": "The environment variable 'DB_HOST' is missing or empty in your .env file.",
  "code": "E002"
}
```

---

## 📊 Changelog

### v2.1.1 (2025-07)

* PHP 8.3 support
* OAuth providers expanded
* Docker support enhanced
* Cache abstraction with fallback

### v2.1.0 (2025-06)

* JWT authentication module
* Telegram alerts with full trace
* Restructure of CLI commands

---

## 📋 License

**MIT License**
Developed by [Janderson Garcia](https://github.com/jandersongarcia)
