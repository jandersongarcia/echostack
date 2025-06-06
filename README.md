# EchoAPI

EchoAPI é um microstack PHP minimalista, projetado para APIs enxutas, rápidas e fáceis de manter. Backend em PHP puro, frontend livre, comunicação via JSON, sem overhead de frameworks pesados.

[Repositório Oficial no GitHub](https://github.com/jandersongarcia/EchoAPI)

---

## Visão Geral

* **Backend**: PHP 8.x
* **Frontend**: Livre (JS, React, Vue, etc)
* **Comunicação**: API REST (JSON)
* **Autoload**: PSR-4 via Composer
* **Banco de Dados**: Medoo (abstração PDO)
* **Roteamento**: AltoRouter
* **Logs**: Monolog
* **Validação**: Respect\Validation
* **Ambiente**: Dotenv

---

## Estrutura do Projeto

```
project-root/
│
├── app/                # Pasta exposta ao servidor web (ponto de entrada)
│   ├── api/            # Endpoints da API do projeto (index.php, rotas públicas)
│   └── frontend/       # (opcional) Arquivos estáticos do frontend (React, Vue, etc)
│
├── bootstrap/          # Código de inicialização e bootstrap da aplicação
│
├── config/             # Arquivos de configuração (DB, API keys, etc)
│
├── logs/               # Arquivos de log (gerados pelo Monolog)
│
├── middleware/         # Middlewares personalizados (ex: autenticação, CORS)
│
├── routes/             # Definição das rotas da aplicação (ex: web.php, api.php)
│
├── scripts/            # Scripts utilitários (ex: geração de API keys)
│
├── src/                # Código fonte principal da aplicação
│   ├── Controllers/    # Controladores (lógica de entrada das rotas)
│   ├── Core/           # Núcleo da aplicação (ex: Kernel, Providers, Containers)
│   ├── Models/         # Modelos de dados (representação das tabelas)
│   ├── Services/       # Regras de negócio e serviços da aplicação
│   └── Utils/          # Funções auxiliares e helpers
│
├── vendor/             # Dependências gerenciadas pelo Composer
│
├── .env                # Variáveis de ambiente (API keys, credenciais, configs)
├── composer.json       # Configuração de dependências e autoload
└── README.md           # Documentação do projeto
```

**Nota:** A pasta `app/` pode opcionalmente conter o frontend da aplicação (React, Vue, Angular, etc), permitindo servir API e UI no mesmo domínio durante o desenvolvimento ou produção simples.

---

## Sistema de Logs

O EchoAPI possui um sistema de logs estruturado, utilizando **Monolog 3.x**, para facilitar monitoramento, debugging e auditoria de segurança.

### Localização dos logs

Os arquivos de log ficam na pasta:

```
project-root/logs/
```

### Arquivos de log

| Arquivo          | Níveis capturados                 | Descrição                                                                                                                                            |
| ---------------- | --------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| **app.log**      | DEBUG, INFO, NOTICE               | Registro geral de operações da aplicação: inicializações, chamadas de API, execuções normais e mensagens de desenvolvimento.                         |
| **errors.log**   | ERROR, CRITICAL, ALERT, EMERGENCY | Erros críticos, falhas de execução, exceções não tratadas e problemas de runtime. Essencial para troubleshooting.                                    |
| **security.log** | WARNING até CRITICAL              | Tentativas inválidas de autenticação, falhas de autorização e atividades suspeitas de segurança. Auxilia em auditorias e investigação de incidentes. |

### Observações

* Certifique-se de conceder permissões de escrita na pasta `logs/` após a instalação.
* Em ambiente de produção, recomenda-se implementar política de rotação de logs para evitar crescimento descontrolado dos arquivos.

### Teste rápido de logs

O EchoAPI inclui um script utilitário para testar a escrita de logs em todos os níveis.

Para executar o teste de logs:

```bash
composer run-script log:test
```

Este comando irá:

* Gerar mensagens de log em todos os níveis (DEBUG, INFO, WARNING, ERROR, CRITICAL, etc)
* Validar se os arquivos `app.log`, `errors.log` e `security.log` estão sendo gerados corretamente.
* Permitir verificar se o roteamento de níveis e handlers do Monolog está operando conforme o esperado.

---

## Endpoint de Health Check

O EchoAPI disponibiliza um endpoint de **verificação de saúde da aplicação**, últil para:

* Monitoramento (UptimeRobot, Pingdom, etc)
* Load Balancers
* Orquestradores (Kubernetes, Docker)
* CI/CD Pipelines

### Endpoint

```http
GET /v1/health
```

### Resposta

Exemplo de resposta completa:

```json
{
  "pong": true,
  "database": "ok",
  "filesystem": "ok",
  "telegram": "configured",
  "version": "1.0.0"
}
```

### O que cada campo representa:

| Campo          | Significado                                                         |
| -------------- | ------------------------------------------------------------------- |
| **pong**       | Health básico da API (sempre `true` se a API respondeu)             |
| **database**   | Verifica se há conexão ativa com o banco                            |
| **filesystem** | Verifica se a pasta de logs está gravável                           |
| **telegram**   | Verifica se as variáveis de ambiente do Telegram estão configuradas |
| **version**    | Exibe a versão da aplicação (definida em `config/version.php`)      |

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

### 3. Configure as variáveis de ambiente

O repositório já contém um arquivo de exemplo chamado `.env_root`. Renomeie-o para `.env`:

```bash
mv .env_root .env
```

Em seguida, edite o arquivo `.env` e preencha com as informações corretas do banco de dados e a chave de API:

```ini
API_KEY=suachavesecreta
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=echoapi
DB_USER=root
DB_PASS=senha
```

### 4. Permissões (opcional)

Garanta permissão de escrita para logs, se houver:

```bash
mkdir logs
chmod -R 775 logs
```

---

## Executando o servidor de desenvolvimento

```bash
php -S localhost:8080 -t public
```

A API ficará acessível em: `http://localhost:8080`

---

## Fluxo básico de requisição

1. **Cliente** envia requisição HTTP para o endpoint
2. **index.php (public/)** é o front-controller e inicia o autoload
3. **Router** verifica a rota em `routes/web.php`
4. **Middleware** valida a chamada e autentica (se aplicável)
5. **Controller** processa a lógica de negócio
6. **Resposta** enviada em JSON

---

## Exemplo simples de rota

Em `routes/web.php`:

```php
$router->map('GET', '/ping', function() {
    header('Content-Type: application/json');
    echo json_encode(['pong' => true]);
});
```

### Testando a rota

Como o EchoAPI está configurado para trabalhar com versionamento de API, o endpoint estará disponível em:

```bash
curl http://localhost:8080/v1/ping
```

Resposta:

```json
{"pong": true}
```

---

## Dependências principais (composer.json)

```json
"require": {
    "vlucas/phpdotenv": "^5.5",
    "respect/validation": "^2.2",
    "symfony/http-foundation": "^6.0",
    "altorouter/altorouter": "^2.0",
    "catfan/medoo": "^2.1",
    "monolog/monolog": "^3.0"
}
```

---

## Scripts auxiliares

### Geração de API Keys

O EchoAPI utiliza **API Keys** como forma de autenticação e controle de acesso aos seus endpoints.

A API Key é uma chave única que deve ser enviada junto às requisições para autorizar o acesso:

Exemplo de envio no header:

```http
Authorization: Bearer SUA_API_KEY
```

Para gerar uma nova chave, execute o seguinte comando:

```bash
composer run-script generate-apikey
```

> ⚠ A chave gerada é automaticamente atualizada no arquivo `.env`. Não é necessário editar manualmente.

Essa camada de segurança evita acessos não autorizados e permite maior controle sobre quem está utilizando a API.

---

## Notificações de Erros via Telegram

O EchoAPI permite o envio automático de mensagens de erro para o Telegram, através de integração nativa com o Monolog.

### Habilitação

Por padrão, a integração com o Telegram é opcional. Basta configurar as variáveis no arquivo `.env`:

```ini
TELEGRAM_BOT_TOKEN=seu_token_aqui
TELEGRAM_CHAT_ID=seu_chat_id_aqui
ERROR_NOTIFY_CATEGORIES=critical,error,alert
```

* `TELEGRAM_BOT_TOKEN`: Token de acesso do seu bot no Telegram.
* `TELEGRAM_CHAT_ID`: ID do usuário ou grupo que irá receber as mensagens.
* `ERROR_NOTIFY_CATEGORIES`: Define quais categorias de log serão enviadas ao Telegram.

> ⚠ Se essas variáveis não estiverem preenchidas, a integração será automaticamente desativada.

---

### Como obter o BOT\_TOKEN

1. Abra o Telegram e converse com o **@BotFather**.
2. Execute o comando `/newbot`.
3. Escolha um nome e um username para o seu bot.
4. O BotFather irá fornecer um token no formato:

```
123456789:ABCDefghIJKlmNOPqrSTUvwxYZ
```

Use este token no `TELEGRAM_BOT_TOKEN` do seu `.env`.

---

### Como obter o CHAT\_ID

#### Enviar para usuário (teste rápido)

1. Envie qualquer mensagem ao seu bot.
2. Acesse no navegador:

```
https://api.telegram.org/bot<SEU_BOT_TOKEN>/getUpdates
```

3. No retorno JSON, localize o campo `chat.id` ou `from.id`, que será o seu `TELEGRAM_CHAT_ID`.

#### Enviar para um grupo

1. Adicione o bot ao grupo.
2. Envie uma mensagem no grupo.
3. Acesse novamente:

```
https://api.telegram.org/bot<SEU_BOT_TOKEN>/getUpdates
```

4. No JSON, localize o `chat.id`. Para grupos, o ID normalmente começa com `-100`:

Exemplo:

```json
"chat": {
    "id": -1001234567890
}
```

Neste caso:

```ini
TELEGRAM_CHAT_ID=-1001234567890
```

---

### Exemplo completo de configuração:

```ini
TELEGRAM_BOT_TOKEN=123456789:ABCDefghIJKlmNOPqrSTUvwxYZ
TELEGRAM_CHAT_ID=-1001234567890
ERROR_NOTIFY_CATEGORIES=critical,error
```

Assim, apenas erros dos níveis `critical` e `error` serão notificados.

---

### 🔒 Observação de segurança:

* **Nunca compartilhe seu BOT\_TOKEN publicamente.**
* Use um chat de teste antes de ativar em produção.

## Licença

MIT

---

Desenvolvido por [JandersonGarcia](https://github.com/jandersongarcia)
