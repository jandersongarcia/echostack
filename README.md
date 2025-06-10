# EchoAPI - Lightweight Microstack for Clean PHP APIs

EchoAPI is a minimalist microstack designed for building REST APIs in PHP with speed, organization, and low coupling.  
It works as a backend toolbox — offering just the essentials to handle routing, database, validation, authentication, and logging.  
Perfect for those who want to skip complex frameworks and focus on a lightweight, functional, and easy-to-maintain API.

It provides basic support for:

* Routing with AltoRouter
* Lightweight ORM with Medoo
* Validation with Respect\Validation
* Logging with Monolog
* Authentication via API Key
* Optional integration with Telegram

---

## Technologies Used

* PHP 8.x
* Medoo (PDO wrapper)
* AltoRouter (routing)
* Monolog (logs)
* Respect\Validation (validation)
* Symfony Console (CLI scripts)
* vlucas/phpdotenv (environment)

---

## Project Structure

```txt
project-root/
├── app/                # Optional frontend and documentation
│   ├── api/            # API v1 response directory
│   ├── docs/           # openapi.json file (Swagger)
│   └── example/        # Sample React application
├── bootstrap/          # Application bootstrapping
├── config/             # Environment and database settings
├── core/               # EchoAPI core
│   ├── Scripts/        # CLI scripts (make, delete, etc)
│   └── Dispatcher.php  # Main kernel
├── logs/               # Log files
├── middleware/         # Custom middleware
├── routes/             # Route definitions (web.php)
├── src/                # Main application code
│   ├── Controllers/    # REST Controllers
│   ├── Models/         # Database-based models
│   ├── Services/       # Business logic
│   └── Validators/     # Custom validation
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

# Install backend dependencies
composer install

# Copy environment file
cp .env_root .env

# Edit .env with your database configuration

# Set permissions for the logs folder (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Executing an Endpoint

EchoAPI handles requests using a clean and straightforward flow:

1. Client sends a request (e.g. `GET /v1/health`)
2. The `public/index.php` is the entry point
3. Middlewares are loaded (authentication, CORS, API Key, etc.)
4. Route is resolved by AltoRouter
5. Controller handles the logic and returns a JSON response

### Sample route

```php
$router->map('GET', '/health', function() {
    echo json_encode(['pong' => true]);
});
```

### Test via terminal

```bash
curl http://localhost:8080/v1/health
```

### Expected response

```json
{
  "pong": true,
  "database": "ok",
  "filesystem": "ok",
  "telegram": "configured",
  "version": "2.0.0"
}
```

---

## API Key Authentication

EchoAPI secures endpoints using API Key authentication.

### Generate API Key

```bash
composer generate:apikey
```

### Use in requests

```http
Authorization: Bearer YOUR_API_KEY
```

If the key is missing or incorrect, a 401 HTTP error will be returned.

---

## Automated CRUD

EchoAPI can generate full CRUD structures based on a database table.

### Generate

```bash
composer make:crud users
```

This creates:

* `src/Models/User.php`
* `src/Services/UserService.php`
* `src/Controllers/UserController.php`
* Routes inside `routes/web.php`

### Delete

```bash
composer delete:crud users
```

### List existing CRUDs + routes

```bash
composer list:crud
```

> The script checks existence before overwriting files or routes.

---

## Swagger Documentation

API documentation is generated via PHP annotations.

### Generate

```bash
composer swagger:build
```

Creates `app/docs/openapi.json`.

### View

Use tools like:

* [Swagger Editor](https://editor.swagger.io/)

---

## Telegram Error Alerts

EchoAPI can notify you on Telegram in case of critical failures.

### Configure in `.env`

```ini
TELEGRAM_BOT_TOKEN=your_token
TELEGRAM_CHAT_ID=your_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

> Useful for quick monitoring in production.

---

## Available Scripts

| Command            | Description                                                |
| ------------------ | ---------------------------------------------------------- |
| `make:module`      | Generates a basic module (Controller, Service, Model)      |
| `delete:module`    | Deletes the specified module                               |
| `make:crud`        | Generates a CRUD with Model, Service, Controller, and routes |
| `delete:crud`      | Deletes the generated CRUD                                 |
| `list:crud`        | Lists all registered CRUDs and routes                      |
| `generate:apikey`  | Creates a new API Key                                      |
| `log:test`         | Creates a sample log                                       |
| `telegram:test`    | Sends a test message to Telegram                           |
| `swagger:build`    | Generates OpenAPI documentation                            |

---

## Example Usage with React

Inside the `app/example` directory, you'll find a **React + Vite** frontend that interacts with the EchoAPI to manage tasks (To Do).

### 1. Set up the database

Create the database and run:

```sql
CREATE TABLE todo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task VARCHAR(255) NOT NULL,
  status ENUM('pending', 'done') DEFAULT 'pending',
  favorite TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME DEFAULT NULL
);
```

### 2. Configure `.env`

Edit your `.env` and set the database credentials:

```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_db
DB_USER=your_user
DB_PASS=your_password
```

### 3. Generate CRUD and API Key

```bash
composer generate:apikey
composer make:crud todo
```

The API key will be available in the root `.env` file.

### 4. Run the React frontend

```bash
cd app/example
npm install
npm run dev
```

Open [http://localhost:5173](http://localhost:5173) in your browser.

### 5. Configure API URL and Key in frontend

In the React `.env`:

```env
VITE_API_URL=http://localhost:8080
VITE_API_KEY=your_api_key_here
```

---

## 🚀 Full Test

After completing the steps above:

1. Access the API at: `http://localhost:8080/v1/todo`
2. Use the To Do interface at: `http://localhost:5173`
3. Add, list, and mark tasks using the React app connected to EchoAPI

---

## License

MIT  
Developed by [Janderson Garcia](https://github.com/jandersongarcia)
