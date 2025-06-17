# EchoAPI - Microstack PHP para APIs Enxutas

O EchoAPI é uma estrutura minimalista (microstack) para quem quer construir APIs REST em PHP com rapidez, organização e baixo acoplamento.
Ele funciona como uma toolbox para backend — oferecendo apenas o essencial para rotas, banco, validações, autenticação e logs.
Ideal para quem quer fugir de frameworks complexos e focar em uma API funcional, leve e fácil de manter.

Ele fornece suporte básico para:

* Roteamento com AltoRouter
* ORM leve com Medoo
* Validação com Respect\Validation
* Logs com Monolog
* Autenticação por API Key
* Autenticação JWT (Opcional)
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
* Firebase PHP-JWT (Autenticação JWT)

---

## Estrutura de Diretórios

```txt
project-root/
├── app/                # Frontend (opcional) e documentação
│   ├── api/            # Arquivo de acesso ao backend
│   └── docs/           # Arquivo gerado da documentação OpenAPI
├── bootstrap/          # Inicialização da aplicação
├── config/             # Configurações de ambiente e banco
├── core/               # Núcleo do EchoAPI
│   ├── Helpers/        # Funções auxiliares genéricas
│   ├── Migration/      # Scripts de instalação, rollback ou atualização de banco
│   ├── OpenApi/        # Configurações e bootstrap da geração Swagger/OpenAPI
│   ├── Scripts/        # Scripts CLI (make, delete, etc)
│   ├── Services/       # Serviços internos
│   ├── Utils/          # Classes utilitárias internas ao Core
│   └── Dispatcher.php  # Kernel principal
├── logs/               # Arquivos de log
├── middleware/         # Middlewares personalizados
├── routes/             # Arquivo de rotas (web.php)
├── src/                # Código principal da aplicação
│   ├── Controllers/    # Controllers REST
│   ├── Docs/           # Anotações Swagger para endpoints
│   ├── Models/         # Modelos baseados no banco
│   ├── Services/       # Lógica de negócio
│   ├── Utils/          # Helpers específicos do projeto
│   ├── Validators/     # Validações customizadas
│   └── Views/          # Templates de saída
│     └── emails/       # Templates de email (ex: recuperação de senha, boas-vindas)
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
cp .env_root .env

# Edite o arquivo .env com as configurações do banco

# Configure permissões para a pasta de logs (Linux/macOS)
mkdir logs
chmod -R 775 logs
```

---

## Execução de um Endpoint

Fluxo padrão de requisição:

1. Cliente envia uma requisição (ex: `GET /v1/health`)
2. O `public/index.php` é o ponto de entrada
3. Middlewares (Auth, API Key, etc) são carregados
4. A rota é resolvida
5. O Controller responde com JSON

### Teste via terminal:

```bash
curl http://localhost:8080/v1/health
```

---

## Autenticação via API Key

```bash
composer generate:apikey
```

Use nas requisições:

```http
Authorization: Bearer SUA_API_KEY
```

---

## CRUD Automatizado

### Criar

```bash
composer make:crud usuarios
```

### Deletar

```bash
composer delete:crud usuarios
```

### Listar

```bash
composer list:crud
```

---

## Autenticação JWT (Opcional)

### Gerar o sistema de autenticação

```bash
composer make:auth
```

Cria Controllers, Services, Middlewares e rotas.

---

### Criar as tabelas no banco (migrations)

```bash
composer migration:auth
```

Cria as tabelas:

* `users`
* `tokens`
* `password_resets`

---

### Deletar o sistema de autenticação

```bash
composer delete:auth
```

---

### Endpoints Padrão do Auth JWT

| Método | Endpoint          | Função                  |
| ------ | ----------------- | ----------------------- |
| POST   | /v1/auth/login    | Login com email/senha   |
| POST   | /v1/auth/register | Registro de usuário     |
| POST   | /v1/auth/recover  | Solicitar reset senha   |
| POST   | /v1/auth/reset    | Resetar senha via token |
| POST   | /v1/auth/logout   | Logout do usuário       |

Após login, o sistema retorna um JWT:

```http
Authorization: Bearer SEU_JWT_AQUI
```

---

## Geração de Documentação (Swagger)

```bash
composer swagger:build
```

Gera `app/docs/openapi.json`

---

## Integração com Telegram

Configuração `.env`:

```ini
TELEGRAM_BOT_TOKEN=seu_token
TELEGRAM_CHAT_ID=seu_chat_id
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

---

## Scripts Disponíveis

| Comando           | Função                                                     |
| ----------------- | ---------------------------------------------------------- |
| `make:module`     | Gera um módulo básico (Controller, Service, Model)         |
| `delete:module`   | Remove os arquivos do módulo informado                     |
| `make:crud`       | Cria Model, Service, Controller e rotas com base em tabela |
| `delete:crud`     | Exclui o CRUD gerado                                       |
| `list:crud`       | Lista todos os CRUDs + rotas registradas                   |
| `make:auth`       | Gera o sistema de autenticação JWT                         |
| `migration:auth`  | Executa as migrations SQL do Auth                          |
| `delete:auth`     | Remove o sistema de autenticação JWT                       |
| `generate:apikey` | Cria nova API Key                                          |
| `log:test`        | Gera logs de exemplo                                       |
| `telegram:test`   | Testa envio de mensagens via Telegram                      |
| `swagger:build`   | Gera documentação OpenAPI                                  |

---

## Licença

MIT
Desenvolvido por [Janderson Garcia](https://github.com/jandersongarcia)