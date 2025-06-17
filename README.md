# EchoAPI - Lightweight PHP Microstack for REST APIs

EchoAPI is a minimalist microstack for developers who want to build REST APIs in PHP quickly, with organization and low coupling.
It works like a backend toolbox — offering only the essentials for routing, database, validation, authentication, and logging.
Perfect for those who want to avoid heavy frameworks and focus on a functional, lightweight, and easy-to-maintain API.

It provides basic support for:

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
├── app/                # Optional frontend (e.g., React example) and documentation
│   ├── api/            # Public entry point for the backend
│   └── docs/           # Generated OpenAPI documentation file
├── bootstrap/          # Application initialization
├── config/             # Environment and database configurations
├── core/               # EchoAPI's core engine
│   ├── Helpers/        # General-purpose helper functions
│   ├── Migration/      # Database install, rollback, or update scripts
│   ├── OpenApi/        # Swagger/OpenAPI configuration and bootstrap
│   ├── Scripts/        # CLI scripts (make, delete, etc.)
│   ├── Services/       # Internal services
│   ├── Utils/          # Core utility classes
│   └── Dispatcher.php  # Main kernel
├── logs/               # Log files
├── middleware/         # Custom middlewares
├── routes/             # Routes file (web.php)
├── src/                # Main application code
│   ├── Controllers/    # REST Controllers
│   ├── Docs/           # Swagger annotations for endpoints
│   ├── Models/         # Database models
│   ├── Services/       # Business logic
│   ├── Utils/          # Project-specific helpers
│   ├── Validators/     # Custom validations
│   └── Views/          # Output templates
│     └── emails/       # Email templates (e.g., password reset, welcome)
├── .env                # Environment variables
├── composer.json       # Dependencies and scripts
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
3. Middlewares (Auth, API Key, etc.) are loaded
4. The route is resolved
5. The Controller responds with JSON

### Test via terminal:

```bash
curl http://localhost:8080/v1/health
```

---

## API Key Authentication

EchoAPI offers a simple **API Key** authentication system, ideal for protecting endpoints without the complexity of JWT or OAuth.

### Generate a new API Key

```bash
composer generate:apikey
```

> **Note:**
> When you run this command, EchoAPI will generate a new random key and automatically fill the `SECRET_KEY` field in the file:

```txt
.env  (at the root of the project)
```

### How to use the API Key in requests

Add the **Authorization** header to all protected requests:

```http
Authorization: Bearer YOUR_API_KEY
```

If the key is incorrect or missing, the API will return an HTTP 401 (Unauthorized) error.

---

## Automated CRUD

EchoAPI allows you to quickly generate a complete CRUD based on an existing table in your database.
This feature saves time by automatically creating the **Model**, **Service**, **Controller**, and the corresponding route.

> **Important:**
> For this command to work, your database must be accessible and the table must already exist.

### Create a CRUD

```bash
composer make:crud users
```

This command will generate:

* `src/Models/Users.php`
* `src/Services/UsersService.php`
* `src/Controllers/UsersController.php`
* Route entries in `routes/web.php`

---

### Delete a CRUD

```bash
composer delete:crud users
```

Removes all files related to the specified CRUD (Model, Service, Controller, and route).

---

### List existing CRUDs

```bash
composer list:crud
```

Displays a list of all generated CRUDs and their corresponding routes.

---

## JWT Authentication (Optional)

### Generate the authentication system

```bash
composer make:auth
```

Creates Controllers, Services, Middlewares, and routes.

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
Authorization: Bearer YOUR_JWT_HERE
```

---

## Swagger Documentation Generation

```bash
composer swagger:build
```

This command will generate the following file:

```txt
app/docs/openapi.json
```

> **Important:**
> To view the documentation in your browser, you need to set the correct API URL in the following file:

```txt
app/docs/swagger-initializer.js
```

Edit the line that defines the Swagger URL to point to your actual `openapi.json` path. Example:

```javascript
window.ui = SwaggerUIBundle({
  url: "http://filedow.net/docs/openapi.json",  // 🔴 Change this to match your environment
  dom_id: '#swagger-ui',
  deepLinking: true,
  presets: [
    SwaggerUIBundle.presets.apis,
    SwaggerUIStandalonePreset
  ],
  plugins: [
    SwaggerUIBundle.plugins.DownloadUrl
  ],
  layout: "StandaloneLayout"
});
```

Once configured, open Swagger UI in your browser (e.g., `http://localhost:8080/app/docs/`).

---

## Telegram Integration

Configure your `.env`:

```ini
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## Available Scripts

| Command           | Function                                                       |
| ----------------- | -------------------------------------------------------------- |
| `make:module`     | Generate a basic module (Controller, Service, Model)           |
| `delete:module`   | Remove the specified module files                              |
| `make:crud`       | Create Model, Service, Controller, and routes based on a table |
| `delete:crud`     | Delete the generated CRUD                                      |
| `list:crud`       | List all CRUDs + registered routes                             |
| `make:auth`       | Generate the JWT authentication system                         |
| `migration:auth`  | Run SQL migrations for Auth                                    |
| `delete:auth`     | Remove the generated authentication system                     |
| `generate:apikey` | Create a new API Key                                           |
| `log:test`        | Generate example logs                                          |
| `telegram:test`   | Send a test message to Telegram                                |
| `swagger:build`   | Generate OpenAPI documentation                                 |

---

## License

MIT
Developed by [Janderson Garcia](https://github.com/jandersongarcia)
