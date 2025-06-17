# EchoAPI - Microstack PHP para APIs Ligeras

EchoAPI es una microestructura minimalista para quienes quieren construir APIs REST en PHP de forma rápida, organizada y con bajo acoplamiento.
Funciona como una toolbox de backend, ofreciendo solo lo esencial para enrutamiento, base de datos, validación, autenticación y logs.
Perfecto para quienes quieren evitar frameworks pesados y centrarse en una API funcional, ligera y fácil de mantener.

Ofrece soporte básico para:

* Enrutamiento con AltoRouter
* ORM ligero con Medoo
* Validación con Respect\Validation
* Logs con Monolog
* Autenticación por API Key
* Autenticación JWT (Opcional)
* Integración opcional con Telegram

---

## Tecnologías Utilizadas

* PHP 8.x
* Medoo (PDO wrapper)
* AltoRouter (enrutamiento)
* Monolog (logs)
* Respect\Validation (validación)
* Symfony Console (scripts CLI)
* vlucas/phpdotenv (variables de entorno)
* Firebase PHP-JWT (Autenticación JWT)

---

## Estructura de Directorios

```txt
project-root/
├── api/                # Punto de entrada público para el backend (index.php)
├── app/                # Frontend opcional (ejemplo en React) + Documentación Swagger
│   └── docs/           # Documentación OpenAPI generada (openapi.json)
├── bootstrap/          # Inicialización de la aplicación
├── config/             # Configuraciones de entorno y base de datos
├── core/               # Motor interno de EchoAPI
│   ├── Helpers/        # Funciones utilitarias generales
│   ├── Migration/      # Scripts de instalación, rollback o actualización de base de datos
│   ├── OpenApi/        # Configuración y bootstrap para Swagger/OpenAPI
│   ├── Scripts/        # Scripts CLI (make, delete, etc)
│   ├── Services/       # Servicios internos
│   ├── Utils/          # Clases utilitarias internas del Core
│   └── Dispatcher.php  # Kernel principal (carga rutas y middlewares)
├── logs/               # Archivos de log
├── middleware/         # Middlewares personalizados (Auth, CORS, API Key, etc)
├── routes/             # Archivo de rutas (web.php)
├── src/                # Código principal de la aplicación
│   ├── Controllers/    # Controllers REST
│   ├── Docs/           # Anotaciones Swagger para endpoints
│   ├── Models/         # Modelos de base de datos
│   ├── Services/       # Lógica de negocio
│   ├── Utils/          # Helpers específicos del proyecto
│   ├── Validators/     # Validaciones personalizadas
│   └── Views/          # Plantillas de salida (emails, etc)
│     └── emails/       # Plantillas de email (recuperación de contraseña, bienvenida, etc)
├── .env                # Variables de entorno
├── composer.json       # Dependencias y scripts CLI
└── README.md           # Documentación del proyecto
```

---

## Instalación

```bash
# Clona el repositorio
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI

# Instala las dependencias
composer install

# Copia el archivo de entorno
cp .env_root .env

# Edita el archivo .env con tus datos de base de datos

# Configura permisos para la carpeta de logs (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Ejecución de un Endpoint

Flujo estándar de una solicitud:

1. El cliente envía una solicitud (ejemplo: `GET /v1/health`)
2. `public/index.php` actúa como punto de entrada
3. Se cargan los middlewares (Auth, API Key, etc)
4. Se resuelve la ruta
5. El Controller responde en formato JSON

### Test en terminal:

```bash
curl http://localhost:8080/v1/health
```

---

## Autenticación por API Key

```bash
composer generate:apikey
```

Usa la key en las solicitudes:

```http
Authorization: Bearer TU_API_KEY
```

---

## CRUD Automatizado

### Crear

```bash
composer make:crud usuarios
```

### Eliminar

```bash
composer delete:crud usuarios
```

### Listar

```bash
composer list:crud
```

---

## Autenticación JWT (Opcional)

### Generar el sistema de autenticación

```bash
composer make:auth
```

Crea Controllers, Services, Middlewares y rutas.

---

### Ejecutar migraciones para el Auth

```bash
composer migration:auth
```

Crea las tablas:

* `users`
* `tokens`
* `password_resets`

---

### Eliminar el sistema de autenticación

```bash
composer delete:auth
```

---

### Endpoints por defecto del Auth JWT

| Método | Endpoint          | Función                                 |
| ------ | ----------------- | --------------------------------------- |
| POST   | /v1/auth/login    | Inicio de sesión con email/contraseña   |
| POST   | /v1/auth/register | Registro de usuario                     |
| POST   | /v1/auth/recover  | Solicitud de recuperación de contraseña |
| POST   | /v1/auth/reset    | Restablecer contraseña con token        |
| POST   | /v1/auth/logout   | Cerrar sesión                           |

Después del login, el sistema devuelve un JWT:

```http
Authorization: Bearer TU_JWT_AQUI
```

---

## Generación de Documentación (Swagger)

```bash
composer swagger:build
```

Genera `app/docs/openapi.json`

---

## Integración con Telegram (Opcional)

Configuración en el `.env`:

```ini
TELEGRAM_BOT_TOKEN=tu_token
TELEGRAM_CHAT_ID=tu_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## Scripts Disponibles

| Comando           | Función                                              |
| ----------------- | ---------------------------------------------------- |
| `make:module`     | Genera un módulo básico (Controller, Service, Model) |
| `delete:module`   | Elimina los archivos del módulo especificado         |
| `make:crud`       | Crea Model, Service, Controller y rutas según tabla  |
| `delete:crud`     | Elimina el CRUD generado                             |
| `list:crud`       | Lista todos los CRUDs + rutas registradas            |
| `make:auth`       | Genera el sistema de autenticación JWT               |
| `migration:auth`  | Ejecuta las migraciones SQL del Auth                 |
| `delete:auth`     | Elimina el sistema de autenticación JWT              |
| `generate:apikey` | Crea una nueva API Key                               |
| `log:test`        | Genera logs de ejemplo                               |
| `telegram:test`   | Envía un mensaje de prueba a Telegram                |
| `swagger:build`   | Genera la documentación OpenAPI                      |

---

## Licencia

MIT
Desarrollado por [Janderson Garcia](https://github.com/jandersongarcia)
