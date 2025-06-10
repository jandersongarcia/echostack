# EchoAPI - Microstack PHP para APIs Ligeras y Organizadas

EchoAPI es un microstack minimalista diseñado para construir APIs REST en PHP de forma rápida, organizada y con bajo acoplamiento.  
Funciona como una caja de herramientas para backend — ofreciendo solo lo esencial para gestionar rutas, base de datos, validaciones, autenticación y logs.  
Ideal para quienes quieren evitar frameworks complejos y enfocarse en una API funcional, ligera y fácil de mantener.

Ofrece soporte básico para:

* Enrutamiento con AltoRouter  
* ORM ligero con Medoo  
* Validación con Respect\Validation  
* Logs con Monolog  
* Autenticación mediante API Key  
* Integración opcional con Telegram

---

## Tecnologías Utilizadas

* PHP 8.x  
* Medoo (wrapper PDO)  
* AltoRouter (ruteo)  
* Monolog (logs)  
* Respect\Validation (validación)  
* Symfony Console (scripts CLI)  
* vlucas/phpdotenv (ambiente)

---

## Estructura del Proyecto

```txt
project-root/
├── app/                # Frontend (opcional) y documentación
│   ├── api/            # Directorio de respuesta de la API v1/
│   ├── docs/           # Archivo openapi.json (Swagger)
│   └── example/        # Aplicación de ejemplo en React
├── bootstrap/          # Inicialización de la aplicación
├── config/             # Configuraciones de entorno y base de datos
├── core/               # Núcleo de EchoAPI
│   ├── Scripts/        # Scripts CLI (make, delete, etc.)
│   └── Dispatcher.php  # Núcleo principal
├── logs/               # Archivos de log
├── middleware/         # Middlewares personalizados
├── routes/             # Definiciones de rutas (web.php)
├── src/                # Código principal de la aplicación
│   ├── Controllers/    # Controladores REST
│   ├── Models/         # Modelos basados en la base de datos
│   ├── Services/       # Lógica de negocio
│   └── Validators/     # Validaciones personalizadas
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

# Instalar dependencias del backend
composer install

# Copiar archivo de entorno
cp .env_root .env

# Editar el archivo .env con los datos de la base

# Crear permisos para la carpeta de logs (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Ejecución de un Endpoint

EchoAPI maneja solicitudes de forma directa y clara:

1. El cliente envía una solicitud (ej: `GET /v1/health`)  
2. El archivo `public/index.php` es el punto de entrada  
3. Se cargan los middlewares (autenticación, CORS, API Key, etc.)  
4. AltoRouter resuelve la ruta  
5. El controlador maneja la lógica y retorna una respuesta JSON

### Ejemplo de ruta

```php
$router->map('GET', '/health', function() {
    echo json_encode(['pong' => true]);
});
```

### Prueba vía terminal

```bash
curl http://localhost:8080/v1/health
```

### Respuesta esperada

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

## Autenticación por API Key

EchoAPI protege los endpoints utilizando autenticación por clave API.

### Generar clave

```bash
composer generate:apikey
```

### Usar en las solicitudes

```http
Authorization: Bearer TU_API_KEY
```

Si la clave está ausente o es incorrecta, se retorna un error HTTP 401.

---

## CRUD Automatizado

EchoAPI permite generar CRUDs completos con base en una tabla de la base de datos.

### Generar

```bash
composer make:crud usuarios
```

Esto crea:

* `src/Models/Usuario.php`
* `src/Services/UsuarioService.php`
* `src/Controllers/UsuarioController.php`
* Ruta dentro de `routes/web.php`

### Eliminar

```bash
composer delete:crud usuarios
```

### Listar CRUDs + rutas

```bash
composer list:crud
```

> El script verifica la existencia antes de sobrescribir archivos o rutas.

---

## Documentación Swagger

La documentación de la API se genera mediante anotaciones PHP.

### Generar

```bash
composer swagger:build
```

Crea el archivo `app/docs/openapi.json`.

### Visualizar

Puedes usar herramientas como:

* [Swagger Editor](https://editor.swagger.io/)

---

## Alertas de Errores vía Telegram

EchoAPI puede enviar notificaciones a Telegram ante fallos críticos.

### Configurar en `.env`

```ini
TELEGRAM_BOT_TOKEN=tu_token
TELEGRAM_CHAT_ID=tu_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

> Muy útil para monitoreo en producción.

---

## Scripts Disponibles

| Comando            | Descripción                                                |
| ------------------ | ---------------------------------------------------------- |
| `make:module`      | Crea un módulo básico (Controller, Service, Model)         |
| `delete:module`    | Elimina el módulo especificado                             |
| `make:crud`        | Crea CRUD con Model, Service, Controller y ruta            |
| `delete:crud`      | Elimina un CRUD generado                                   |
| `list:crud`        | Lista todos los CRUDs y rutas registrados                  |
| `generate:apikey`  | Crea una nueva API Key                                     |
| `log:test`         | Genera un log de ejemplo                                   |
| `telegram:test`    | Envía mensaje de prueba a Telegram                         |
| `swagger:build`    | Genera documentación OpenAPI                               |

---

## Ejemplo con React

Dentro de `app/example` encontrarás un frontend hecho con **React + Vite** que se comunica con EchoAPI para gestionar tareas (To Do).

### 1. Crear la base de datos

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

### 2. Configurar el `.env`

```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=tu_bd
DB_USER=tu_usuario
DB_PASS=tu_contraseña
```

### 3. Generar CRUD y API Key

```bash
composer generate:apikey
composer make:crud todo
```

La clave estará disponible en el archivo `.env` en la raíz del proyecto.

### 4. Ejecutar el frontend React

```bash
cd app/example
npm install
npm run dev
```

Abre [http://localhost:5173](http://localhost:5173) en tu navegador.

### 5. Configurar la URL de la API y clave en el frontend

```env
VITE_API_URL=http://localhost:8080
VITE_API_KEY=tu_api_key_aqui
```

---

## 🚀 Prueba Completa

Una vez que completes los pasos anteriores:

1. Accede a la API en: `http://localhost:8080/v1/todo`  
2. Usa la interfaz To Do en: `http://localhost:5173`  
3. Crea, lista y marca tareas con el frontend conectado a EchoAPI

---

## Licencia

MIT  
Desarrollado por [Janderson Garcia](https://github.com/jandersongarcia)
