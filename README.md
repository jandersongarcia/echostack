# EchoAPI 2.0

EchoAPI é um microstack PHP minimalista, projetado para APIs enxutas, rápidas e altamente manuteníveis. Agora com arquitetura profissionalizada, separando claramente o núcleo do sistema (Core) do código da aplicação (App).

---

## 📃 Visão Geral

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

## 🗂 Estrutura do Projeto Atualizada

```
project-root/
│
├── app/                # (reservado para código público - frontend, se houver)
│
├── bootstrap/          # Inicialização e bootstrap da aplicação
│
├── config/             # Configurações de banco, credenciais, etc
│
├── core/               # Núcleo do EchoAPI (NÃO editável)
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

## 🔧 Instalação

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

## 🔍 Health Check com Identidade

### Endpoint

```http
GET /v1/
```

### Resposta

Exemplo:

```
🚁 EchoAPI - version: 2.0.0 | Live long and prosper 🖖
```

O controle de versão e assinatura é centralizado via:

```php
Core\Utils\SystemInfo::fullSignature();
```

A versão é lida automaticamente do `composer.json`:

```json
"extra": {
  "echoapi-version": "2.0.0"
}
```

---

## 🔢 Scripts automatizados

### Geração de Módulos

Cria Controller, Model, Service, Validator e rotas automaticamente:

```bash
composer run make:module NomeDaEntidade
```

### Remoção de Módulos

Deleta todos os arquivos e rotas gerados:

```bash
composer run delete:module NomeDaEntidade
```

### Teste de Logs

Valida o sistema completo de logs:

```bash
composer run log:test
```

### Geração de API Key

```bash
composer run generate:apikey
```

---

## 🌟 Sistema de Logs

Local: `/logs/`

| Arquivo          | Níveis capturados                 |
| ---------------- | --------------------------------- |
| **app.log**      | DEBUG, INFO, NOTICE               |
| **errors.log**   | ERROR, CRITICAL, ALERT, EMERGENCY |
| **security.log** | WARNING até CRITICAL              |

Sistema completo baseado em **Monolog 3.x**.

---

## 🔧 Tecnologias Base

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

## 🔒 Notificações via Telegram (opcional)

Integração via Monolog: permite receber erros diretamente no Telegram.
Totalmente configurável via `.env`.

---

## 💼 Licença

MIT

---

Desenvolvido por [JandersonGarcia](https://github.com/jandersongarcia)
