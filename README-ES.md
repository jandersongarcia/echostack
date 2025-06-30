# EchoAPI - Microstack PHP para APIs Ligeras

EchoAPI es una microestructura minimalista para desarrolladores que quieren construir APIs REST en PHP de forma rápida, organizada y con bajo acoplamiento.
Funciona como una caja de herramientas de backend, ofreciendo solo lo esencial para enrutamiento, base de datos, validación, autenticación y registros (logs).
Perfecto para quienes quieren evitar frameworks pesados y enfocarse en una API funcional, ligera y fácil de mantener.

Proporciona soporte básico para:

* Enrutamiento con AltoRouter
* ORM ligero con Medoo
* Validación con Respect\Validation
* Registros con Monolog
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
├── app/                # Frontend opcional (ejemplo en React) y documentación
│   ├── api/            # Punto de entrada público al backend
│   └── docs/           # Archivo generado de documentación OpenAPI
├── bootstrap/          # Inicialización de la aplicación
├── config/             # Configuraciones de entorno y base de datos
├── core/               # Núcleo interno de EchoAPI
│   ├── Helpers/        # Funciones auxiliares generales
│   ├── Migration/      # Scripts de instalación, rollback o actualización de base de datos
│   ├── OpenApi/        # Configuración y bootstrap de Swagger/OpenAPI
│   ├── Scripts/        # Scripts CLI (make, delete, etc)
│   ├── Services/       # Servicios internos
│   ├── Utils/          # Clases utilitarias internas del Core
│   └── Dispatcher.php  # Kernel principal
├── logs/               # Archivos de logs
├── middleware/         # Middlewares personalizados
├── routes/             # Archivo de rutas (web.php)
├── src/                # Código principal de la aplicación
│   ├── Controllers/    # Controllers REST
│   ├── Docs/           # Anotaciones Swagger para endpoints
│   ├── Models/         # Modelos de base de datos
│   ├── Services/       # Lógica de negocio
│   ├── Utils/          # Helpers específicos del proyecto
│   ├── Validators/     # Validaciones personalizadas
│   └── Views/          # Plantillas de salida
│     └── emails/       # Plantillas de email (recuperación de contraseña, bienvenida, etc)
├── .env                # Variables de entorno
├── composer.json       # Dependencias y scripts
└── README.md           # Documentación del proyecto
```

---

## Instalación

```bash
# Clonar el repositorio
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI

# Instalar dependencias
composer install

# Copiar el archivo de entorno
cp .env_root .env

# Editar el archivo .env con las configuraciones de la base de datos

# Configurar permisos para la carpeta de logs (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Ejecución de un Endpoint

Flujo estándar de una solicitud:

1. El cliente envía una solicitud (por ejemplo: `GET /v1/health`)
2. `public/index.php` actúa como punto de entrada
3. Se cargan los middlewares (Auth, API Key, etc.)
4. La ruta se resuelve
5. El Controller responde con JSON

### Prueba en terminal:

```bash
curl http://localhost:8080/v1/health
```

---

## Autenticación por API Key

EchoAPI ofrece un sistema sencillo de autenticación mediante **API Key**, ideal para proteger endpoints sin la complejidad de JWT u OAuth.

### Generar una nueva API Key

```bash
composer generate:apikey
```

> **Nota:**
> Al ejecutar este comando, EchoAPI generará una clave aleatoria y la insertará automáticamente en el campo `JWT_SECRET` dentro del archivo:

```txt
.env  (en la raíz del proyecto)
```

### Cómo usar la API Key en las solicitudes

Añade el encabezado **Authorization** en todas las solicitudes protegidas:

```http
Authorization: Bearer TU_API_KEY
```

Si la clave es incorrecta o está ausente, la API devolverá un error HTTP 401 (Unauthorized).

---

## CRUD Automatizado

EchoAPI te permite generar rápidamente un CRUD completo basado en una tabla existente en tu base de datos.
Esta función ahorra tiempo creando automáticamente el **Model**, **Service**, **Controller** y el fragmento de ruta correspondiente.

> **Importante:**
> Para que el comando funcione, la base de datos debe estar conectada y la tabla debe existir previamente.

### Crear un CRUD

```bash
composer make:crud users
```

Este comando generará:

* `src/Models/Users.php`
* `src/Services/UsersService.php`
* `src/Controllers/UsersController.php`
* Entradas de ruta en `routes/web.php`

---

### Eliminar un CRUD

```bash
composer delete:crud users
```

Elimina todos los archivos relacionados con el CRUD especificado (Model, Service, Controller y ruta).

---

### Listar CRUDs existentes

```bash
composer list:crud
```

Muestra una lista de todos los CRUDs generados y sus respectivas rutas.

---

## Autenticación JWT (Opcional)

### Generar el sistema de autenticación

```bash
composer make:auth
```

Genera Controllers, Services, Middlewares y rutas.

---

### Crear las tablas en la base de datos (migrations)

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

| Método | Endpoint          | Función                                  |
| ------ | ----------------- | ---------------------------------------- |
| POST   | /v1/auth/login    | Iniciar sesión con email/contraseña      |
| POST   | /v1/auth/register | Registrar nuevo usuario                  |
| POST   | /v1/auth/recover  | Solicitar restablecimiento de contraseña |
| POST   | /v1/auth/reset    | Restablecer contraseña vía token         |
| POST   | /v1/auth/logout   | Cerrar sesión del usuario                |

Después del login, el sistema devuelve un JWT:

```http
Authorization: Bearer TU_JWT_AQUI
```

---

## Generación de Documentación (Swagger)

```bash
composer swagger:build
```

Este comando generará el archivo:

```txt
app/docs/openapi.json
```

> **Importante:**
> Para visualizar la documentación en el navegador, debes configurar la URL correcta de la API en el siguiente archivo:

```txt
app/docs/swagger-initializer.js
```

Edita la línea que define la URL del Swagger para que apunte a tu archivo `openapi.json`. Ejemplo:

```javascript
window.ui = SwaggerUIBundle({
  url: "http://filedow.net/docs/openapi.json",  // 🔴 Cambia esta línea según tu entorno
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

Luego abre Swagger UI en tu navegador (por ejemplo: `http://localhost:8080/app/docs/`).

---

## Integración con Telegram

Configuración en el `.env`:

```ini
TELEGRAM_BOT_TOKEN=tu_token
TELEGRAM_CHAT_ID=tu_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## Scripts Disponibles

| Comando           | Función                                                |
| ----------------- | ------------------------------------------------------ |
| `make:module`     | Genera un módulo básico (Controller, Service, Model)   |
| `delete:module`   | Elimina los archivos del módulo especificado           |
| `make:crud`       | Crea Model, Service, Controller y rutas según la tabla |
| `delete:crud`     | Elimina el CRUD generado                               |
| `list:crud`       | Lista todos los CRUDs y rutas registradas              |
| `make:auth`       | Genera el sistema de autenticación JWT                 |
| `migration:auth`  | Ejecuta las migraciones SQL del Auth                   |
| `delete:auth`     | Elimina el sistema de autenticación JWT                |
| `generate:apikey` | Crea una nueva API Key                                 |
| `log:test`        | Genera logs de ejemplo                                 |
| `telegram:test`   | Envía un mensaje de prueba a Telegram                  |
| `swagger:build`   | Genera documentación OpenAPI                           |

---

## Licencia

MIT
Desarrollado por [Janderson Garcia](https://github.com/jandersongarcia)
