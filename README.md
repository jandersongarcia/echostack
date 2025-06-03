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
|
├── public/            # Pasta exposta ao servidor web (index.php)
|
├── src/               # Código fonte
|   ├── Controllers/   # Controladores
|   ├── Models/        # Modelos de dados
|   ├── Services/      # Regras de negócio
|   └── Utils/         # Funções auxiliares
|
├── middleware/        # Middlewares personalizados
|
├── config/            # Configurações da aplicação
|
├── routes/            # Definição de rotas (web.php)
|
├── scripts/           # Scripts auxiliares (ex: geração de API keys)
|
├── vendor/            # Dependências gerenciadas pelo Composer
|
├── .env               # Variáveis de ambiente (ex: API_KEY, DB)
├── composer.json      # Dependências e autoload
└── README.md          # Documentação
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

> ⚠ Não esqueça de atualizar o valor de `API_KEY` no arquivo `.env` após gerar uma nova chave.

Essa camada de segurança evita acessos não autorizados e permite maior controle sobre quem está utilizando a API.

---

## Licença

MIT

---

Desenvolvido por [JandersonGarcia](https://github.com/jandersongarcia)
