# Instalação do EchoAPI

Este guia cobre o processo de instalação do EchoAPI do zero, incluindo o download do repositório, configuração do ambiente e dependências.

---

## 1. Clonar o Repositório

Clone o projeto diretamente do GitHub:

```bash
git clone https://github.com/jandersongarcia/EchoAPI.git
cd EchoAPI
````

---

## 2. Instalar as Dependências

Utilize o Composer para instalar os pacotes necessários:

```bash
composer install
```

> Certifique-se de ter o PHP 8.x e o Composer instalados corretamente no seu ambiente.

---

## 3. Configurar Variáveis de Ambiente

Crie um arquivo `.env` com base no exemplo `app/.env_root` (ou diretamente com seu conteúdo):

```bash
cp .env_root .env
```

Depois, edite o `.env` com os dados do seu banco de dados e outras configurações:

```ini
APP_ENV=development
APP_DEBUG=true

LANGUAGE=BR

DB_HOST=localhost
DB_PORT=3306
DB_NAME=nome_do_banco
DB_USER=usuario
DB_PASS=senha
```

---

## 4. Criar Pasta de Logs

Garanta que a pasta `logs/` exista e tenha permissões adequadas:

```bash
mkdir logs
chmod -R 775 logs
```

---

## 5. Configurar Servidor Local

Você pode servir a aplicação usando o PHP embutido:

```bash
php -S localhost:8080 -t public/
```

Acesse no navegador:

```
http://localhost:8080
```

---

## 6. Testar Endpoint de Saúde

Verifique se a API está funcionando com o endpoint de saúde:

```bash
curl http://localhost:8080/v1/health
```

Resposta esperada:

```json
{
  "pong": true,
  "database": "ok",
  "filesystem": "ok",
  "telegram": "configured",
  "version": "2.x"
}
```

---

## Próximos passos

* Gerar sua chave de API: `composer generate:apikey`
* Criar um CRUD automatizado: `composer make:crud nome_da_tabela`
* Configurar integração com Telegram (opcional)

---
