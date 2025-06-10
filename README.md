# EchoAPI - Microstack PHP para APIs Enxutas

O EchoAPI é uma estrutura mínimalista (microstack) para quem quer construir APIs REST em PHP com rapidez, organização e baixo acoplamento.
Ele funciona como uma toolbox para backend — ou seja, oferece apenas o essencial para lidar com rotas, banco, validações, autenticação e logs.
Ideal para quem quer fugir de frameworks complexos e focar em uma API funcional, leve e fácil de manter.

Ele fornece suporte básico para:

* Roteamento com AltoRouter
* ORM leve com Medoo
* Validação com Respect\Validation
* Logs com Monolog
* Autenticação por API Key
* Integração opcional com Telegram

---

## Tecnologias Utilizadas

* PHP 8.x
* Medoo (PDO wrapper)
* AltoRouter (roteamento)
* Monolog (logs)
* Respect\Validation (validação)
* Symfony Console (scripts CLI)
* vlucas/phpdotenv (ambiente)

---

## Estrutura de Diretórios

```txt
project-root/
├── app/                # Frontend (opcional) e documentação
│   └── docs/           # Arquivo openapi.json (Swagger)
├── bootstrap/          # Inicialização da aplicação
├── config/             # Configurações de ambiente e banco
├── core/               # Núcleo do EchoAPI
│   ├── Scripts/        # Scripts CLI (make, delete, etc)
│   └── Dispatcher.php  # Kernel principal
├── logs/               # Arquivos de log
├── middleware/         # Middlewares personalizados
├── routes/             # Arquivo de rotas (web.php)
├── src/                # Código principal da aplicação
│   ├── Controllers/    # Controllers REST
│   ├── Models/         # Modelos baseados no banco
│   ├── Services/       # Lógica de negócio
│   └── Validators/     # Validações customizadas
├── .env                # Variáveis de ambiente
├── composer.json       # Dependências e scripts
└── README.md           # Documentação do projeto
```

---

## Instalação

```bash
# Clone o repositório
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI

# Instale as dependências
composer install

# Copie o arquivo de ambiente
touch .env
cp .env_root .env

# Edite o arquivo .env com as configurações do banco

# Configure permissões para a pasta de logs (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Execução de um Endpoint

O EchoAPI segue um fluxo direto para lidar com requisições:

1. Cliente envia uma requisição para a API (ex: `GET /v1/health`)
2. O arquivo `public/index.php` é o ponto de entrada
3. Middlewares são carregados (ex: autenticação, CORS, API Key)
4. A rota é resolvida pelo AltoRouter
5. O Controller manipula a lógica e retorna resposta JSON

### Exemplo de rota

```php
$router->map('GET', '/health', function() {
    echo json_encode(['pong' => true]);
});
```

### Teste via terminal

```bash
curl http://localhost:8080/v1/health
```

### Retorno esperado

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

## Autenticação via API Key

Para proteger seus endpoints, o EchoAPI utiliza autenticação por chave de API.

### Gerar chave de acesso

```bash
composer generate:apikey
```

### Usar nas requisições

```http
Authorization: Bearer SUA_API_KEY
```

Se a chave estiver incorreta ou ausente, será retornado erro HTTP 401.

---

## CRUD Automatizado

O EchoAPI permite gerar rapidamente um CRUD completo com base em uma tabela do banco de dados.

### Gerar

```bash
composer make:crud usuarios
```

Gera os arquivos:

* `src/Models/Usuario.php`
* `src/Services/UsuarioService.php`
* `src/Controllers/UsuarioController.php`
* Rotas no `routes/web.php`

### Deletar

```bash
composer delete:crud usuarios
```

### Listar CRUDs + rotas

```bash
composer list:crud
```

> Os scripts verificam existência antes de sobrescrever arquivos e rotas.

---

## Geração de Documentação (Swagger)

A documentação da API é gerada automaticamente via anotações PHP.

### Gerar

```bash
composer swagger:build
```

Cria o arquivo `app/docs/openapi.json`.

### Visualizar

Use ferramentas como:

* [Swagger Editor](https://editor.swagger.io/)

---

## Integração com Telegram (Alerta de erros)

O EchoAPI pode enviar mensagens para o Telegram em caso de falhas críticas.

### Configuração no `.env`

```ini
TELEGRAM_BOT_TOKEN=seu_token
TELEGRAM_CHAT_ID=seu_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

> Útil para monitoramento rápido em produção.

---

## Scripts Disponíveis

| Comando           | Função                                                     |
| ----------------- | ---------------------------------------------------------- |
| `make:module`     | Gera um módulo básico (Controller, Service, Model)         |
| `delete:module`   | Remove os arquivos do módulo informado                     |
| `make:crud`       | Cria Model, Service, Controller e rotas com base em tabela |
| `delete:crud`     | Exclui o CRUD gerado                                       |
| `list:crud`       | Lista todos os CRUDs + rotas registradas                   |
| `generate:apikey` | Cria nova API Key                                          |
| `log:test`        | Gera logs de exemplo                                       |
| `telegram:test`   | Envia mensagem de teste para o Telegram                    |
| `swagger:build`   | Gera documentação OpenAPI                                  |

---

## Licença

MIT

Desenvolvido por [Janderson Garcia](https://github.com/jandersongarcia)
