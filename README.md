# EchoAPI 2.0

EchoAPI é um microstack PHP minimalista, projetado para APIs enxutas, rápidas e altamente manutenáveis.

---

## Visão Geral

* **Backend**: PHP 8.x
* **Frontend**: Livre (JS, React, Vue, etc)
* **Comunicação**: API REST (JSON)
* **Autoload**: PSR-4 via Composer
* **Banco de Dados**: Medoo (PDO Abstraction)
* **Roteamento**: AltoRouter
* **Logs**: Monolog 3.x
* **Validação**: Respect\Validation
* **Ambiente**: Dotenv

---

## Estrutura do Projeto Atualizada

```
project-root/
│
├── app/                # (reservado para código público - frontend, se houver)
│   ├── api/            # Responsável por conectar ao backend
│   └── docs/           # Documentação gerada (openapi.json)
│
├── bootstrap/          # Inicialização e bootstrap da aplicação
│
├── config/             # Configurações de banco, credenciais, etc
│
├── core/               # Núcleo do EchoAPI (NAO editável)
│   ├── Helpers/        # Helpers centrais (ex: PathResolver)
│   ├── Scripts/        # Scripts de automação (make-module, delete-module)
│   ├── Services/       # Serviços internos do framework
│   ├── Utils/          # Versão, System Info, HealthCheck, etc
│   ├── Dispatcher.php  # Kernel principal
│   └── MiddlewareLoader.php
│
├── logs/               # Logs de aplicação
│
├── middleware/         # Middlewares customizados
│
├── routes/             # Definição de rotas
│
├── src/                # Código da aplicação (personalizado pelo dev)
│   ├── Controllers/    # Controllers de API
│   ├── Models/         # Modelos de dados
│   ├── Services/       # Regras de negócio
│   ├── Validators/     # Validações customizadas
│   └── Utils/          # Helpers adicionais
│
├── vendor/             # Dependências do Composer
│
├── .env                # Variáveis de ambiente
├── composer.json       # Configurações e versão
└── README.md           # Documentação do projeto
```

---

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI
```

### 2. Instale as dependências

```bash
composer install
```

### 3. Configure o ambiente

Renomeie o arquivo *.env\_root* para *.env*

```bash
cp .env_root .env
```

Edite o `.env`:

```ini
API_KEY=suachavesecreta
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=echoapi
DB_USER=root
DB_PASS=senha
```

### 4. Permissões

```bash
mkdir logs
chmod -R 775 logs
```

---

## Fluxo de execução de um endpoint

1. O cliente faz uma requisição HTTP (ex: `GET /v1/health`)
2. `public/index.php` é o Front Controller que inicia autoload e Dispatcher.
3. O `Dispatcher` carrega middlewares (ex: autenticação, CORS, validação de API Key).
4. O `AltoRouter` resolve a rota com base no arquivo `routes/`.
5. O Controller correspondente é chamado.
6. Controller aciona regras de negócio via Services e Models.
7. A resposta é enviada ao cliente em JSON.

---

## Exemplo simples de rota

Arquivo: `routes/web.php`

```php
$router->map('GET', '/health', function() {
    header('Content-Type: application/json');
    echo json_encode(['pong' => true]);
});
```

Teste local:

```bash
curl http://localhost:8080/v1/health
```

Resposta:

```json
{"pong":true,"database":"ok","filesystem":"ok","telegram":"configured","version":"2.x.x"}
```

---

## Autenticação via API Key

O EchoAPI suporta autenticação de chamadas usando API Key.

Para criar uma chave secreta, use o comando no prompt

```bash
composer generate:apikey
```

Inclua o header nas requisições:

```http
Authorization: Bearer SUA_API_KEY
```

Se a chave estiver ausente ou incorreta, a requisição será bloqueada pelo middleware de autenticação.

---

## Health Check com Identidade

### Endpoint

```http
GET /v1/
```

### Resposta exemplo

```
EchoAPI - version: 2.0.0 | Live long and prosper 🖖
```

Controlado pelo `Core\Utils\SystemInfo::fullSignature()` e pelo campo `extra` no `composer.json`:

```json
"extra": {
  "echoapi-version": "2.0.0"
}
```

---

## Scripts automatizados

### Geração de Módulos

```bash
composer make:module NomeDaEntidade
```

### Remoção de Módulos

```bash
composer delete:module NomeDaEntidade
```

### Teste de Logs

```bash
composer log:test
```

### Geração de API Key

```bash
composer generate:apikey
```

---

## Sistema de Logs

Local: `/logs/`

| Arquivo          | Níveis capturados                 |
| ---------------- | --------------------------------- |
| **app.log**      | DEBUG, INFO, NOTICE               |
| **errors.log**   | ERROR, CRITICAL, ALERT, EMERGENCY |
| **security.log** | WARNING até CRITICAL              |

Sistema baseado em **Monolog 3.x**.

---

## Integração com Telegram

### Configuração no `.env`

```ini
TELEGRAM_BOT_TOKEN=seu_token_aqui
TELEGRAM_CHAT_ID=seu_chat_id_aqui
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

#### Como obter o BOT\_TOKEN

1. Converse com **@BotFather**
2. Use o comando `/newbot`
3. Defina nome e username
4. Obtenha o token:

```
123456789:ABCDefghIJKlmNOPqrSTUvwxYZ
```

#### Como obter o CHAT\_ID

**Para usuário:**

1. Converse com seu bot.
2. Acesse:

```
https://api.telegram.org/bot<SEU_BOT_TOKEN>/getUpdates
```

3. Capture o `chat.id`.

**Para grupos:**

1. Adicione o bot no grupo.
2. Envie mensagem no grupo.
3. Consulte novamente `/getUpdates` para capturar o `chat.id` (começa com `-100`).

---

## Documentação da API (Swagger)

A documentação da API é gerada automaticamente com base nas anotações do Swagger (OpenAPI) nos arquivos do projeto.

### Como gerar a documentação

```bash
composer swagger:build
```

O arquivo será gerado em:

```
app/docs/openapi.json
```

Você pode visualizá-lo usando qualquer visualizador Swagger, como o [Swagger UI](https://editor.swagger.io/), apontando para esse JSON.

---

## Tecnologias Base

```json
"require": {
  "vlucas/phpdotenv": "^5.5",
  "respect/validation": "^2.2",
  "symfony/http-foundation": "^6.0",
  "altorouter/altorouter": "^2.0",
  "catfan/medoo": "^2.1",
  "monolog/monolog": "^3.0",
  "symfony/console": "^7.0"
}
```

---

## Licença

MIT

---

Desenvolvido por [JandersonGarcia](https://github.com/jandersongarcia)
