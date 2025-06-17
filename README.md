# EchoAPI - Lightweight PHP Microstack for REST APIs

EchoAPI is a minimalist microstack for developers who want to build REST APIs in PHP quickly, with organization and low coupling.
It works like a backend toolbox — offering only the essentials for routing, database, validation, authentication, and logging.
Perfect for those who want to avoid heavy frameworks and focus on a functional, lightweight, and easy-to-maintain API.

It offers basic support for:

* Routing with AltoRouter
* Lightweight ORM with Medoo
* Validation with Respect\Validation
* Logging with Monolog
* API Key Authentication
* JWT Authentication (Optional)
* Optional Telegram Integration

---

## Technologies Used

* PHP 8.x
* Medoo (PDO wrapper)
* AltoRouter (routing)
* Monolog (logging)
* Respect\Validation (validation)
* Symfony Console (CLI scripts)
* vlucas/phpdotenv (environment variables)
* Firebase PHP-JWT (JWT Authentication)

---

## Directory Structure

```txt
project-root/
├── api/                # Public entry point for the backend (index.php)
├── app/                # Optional frontend (React example) + Swagger docs
│   └── docs/           # Generated OpenAPI documentation (openapi.json)
├── bootstrap/          # Application initialization
├── config/             # Environment and database configurations
├── core/               # EchoAPI's internal engine
│   ├── Helpers/        # General-purpose utility functions
│   ├── Migration/      # Database install/rollback/update scripts
│   ├── OpenApi/        # Swagger/OpenAPI config and bootstrap
│   ├── Scripts/        # CLI scripts (make, delete, etc)
│   ├── Services/       # Internal infrastructure services
│   ├── Utils/          # Core utility classes
│   └── Dispatcher.php  # Main kernel (loads routes and middlewares)
├── logs/               # Log files
├── middleware/         # Custom middlewares (Auth, CORS, API Key checks)
├── routes/             # Routes file (web.php)
├── src/                # Main application code
│   ├── Controllers/    # REST Controllers
│   ├── Docs/           # Swagger annotations for endpoints
│   ├── Models/         # Database models
│   ├── Services/       # Business logic
│   ├── Utils/          # Project-specific helpers
│   ├── Validators/     # Custom validations
│   └── Views/          # Output templates (emails, etc)
│     └── emails/       # Email templates (password reset, welcome, etc)
├── .env                # Environment variables
├── composer.json       # Dependencies and CLI scripts
└── README.md           # Project documentation
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

# Edit the .env file with your database settings

# Set permissions for the logs folder (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Running an Endpoint

Standard request flow:

1. Client sends a request (e.g., `GET /v1/health`)
2. `public/index.php` acts as the entry point
3. Middlewares (Auth, API Key, etc) are loaded
4. The route is resolved
5. The Controller returns a JSON response

### Test via terminal:

```bash
curl http://localhost:8080/v1/health
```

---

## API Key Authentication

```bash
composer generate:apikey
```

Use the key in your requests:

```http
Authorization: Bearer YOUR_API_KEY
```

---

## Automated CRUD

### Create

```bash
composer make:crud usuarios
```

### Delete

```bash
composer delete:crud usuarios
```

### List

```bash
composer list:crud
```

---

## JWT Authentication (Optional)

### Generate the authentication system

```bash
composer make:auth
```

Generates Controllers, Services, Middlewares, and routes.

---

### Run database migrations for Auth

```bash
composer migration:auth
```

Creates the tables:

* `users`
* `tokens`
* `password_resets`

---

### Delete the authentication system

```bash
composer delete:auth
```

---

### Default JWT Auth Endpoints

| Method | Endpoint          | Function                  |
| ------ | ----------------- | ------------------------- |
| POST   | /v1/auth/login    | Login with email/password |
| POST   | /v1/auth/register | User registration         |
| POST   | /v1/auth/recover  | Request password reset    |
| POST   | /v1/auth/reset    | Reset password via token  |
| POST   | /v1/auth/logout   | Logout the user           |

After login, the system returns a JWT:

```http
Authorization: Bearer YOUR_JWT_TOKEN
```

---

## Swagger Documentation Generation

```bash
composer swagger:build
```

Generates `app/docs/openapi.json`

---

## Telegram Integration (Optional)

Set up your `.env`:

```ini
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## Available Scripts

| Command           | Description                                                    |
| ----------------- | -------------------------------------------------------------- |
| `make:module`     | Generate a basic module (Controller, Service, Model)           |
| `delete:module`   | Remove the specified module files                              |
| `make:crud`       | Create Model, Service, Controller, and routes based on a table |
| `delete:crud`     | Delete the generated CRUD                                      |
| `list:crud`       | List all CRUDs + registered routes                             |
| `make:auth`       | Generate the JWT authentication system                         |
| `migration:auth`  | Run SQL migrations for Auth                                    |
| `delete:auth`     | Remove the generated authentication system                     |
| `generate:apikey` | Generate a new API Key                                         |
| `log:test`        | Generate test logs                                             |
| `telegram:test`   | Send a test message to Telegram                                |
| `swagger:build`   | Build OpenAPI documentation                                    |

---

## License

MIT
Developed by [Janderson Garcia](https://github.com/jandersongarcia)
