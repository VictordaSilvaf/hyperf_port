# Hyperf API — DDD / Hexagonal

API REST em **[Hyperf 3.x](https://hyperf.io)** com organização em camadas (**Domain**, **Application**, **Infrastructure**), autenticação por **Bearer token** assinado (HMAC), registo/login, reset de palavra-passe com código por e-mail (Mailpit em desenvolvimento), validação JSON e testes com **Pest**.

---

## Conteúdo

- [Funcionalidades](#funcionalidades)
- [Stack](#stack)
- [Requisitos](#requisitos)
- [Início rápido com Docker](#início-rápido-com-docker)
- [Desenvolvimento local (sem Docker)](#desenvolvimento-local-sem-docker)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [Base de dados e migrações](#base-de-dados-e-migrações)
- [Serviços auxiliares (compose)](#serviços-auxiliares-compose)
- [Comandos úteis](#comandos-úteis)
- [Testes e análise estática](#testes-e-análise-estática)
- [Documentação](#documentação)
- [Estrutura do repositório](#estrutura-do-repositório)
- [Licença](#licença)

---

## Funcionalidades

- **HTTP** — Prefixo global `/api/v1`; respostas em JSON; erros de validação e HTTP tratados de forma previsível (`APP_DEBUG` controla detalhe em 500).
- **Utilizadores** — Registo, consulta por ID, perfil autenticado (`GET /api/v1/users/me`).
- **Auth** — Login, logout (stateless no servidor), refresh de token (enquanto o token actual for válido), alteração de palavra-passe autenticada.
- **Reset de palavra-passe** — `forgot-password` + `reset-password` com código de 6 dígitos; armazenamento em memória (`array`) ou **Redis**; e-mail via **Symfony Mailer** (SMTP).
- **Persistência** — Repositório de utilizadores em **memória** ou **MySQL** (`APP_USER_REPOSITORY`), configurável por `.env`.
- **RBAC** — Papéis `admin`, `manager`, `user` (seed na migração); permissões granulares; criação de novos papéis; atribuição de permissões a papéis e de papéis a utilizadores (`/api/v1/admin/...`). Após `migrate`, todos os utilizadores existentes recebem o papel `user`. São criados dois utilizadores de desenvolvimento (apenas com MySQL): `admin@victordev.com` (papel `admin`) e `manager@victordev.com` (papel `manager`), ambos com palavra-passe inicial **`VictorDev123!`** — altere ou elimine em produção.
- **Admin — utilizadores** — Listagem, detalhe, criação e edição em `/api/v1/admin/users` (permissões `users.view`, `users.create`, `users.update`; ver `docs/API.md`). Migração `2026_05_23_000000_add_users_create_update_permissions.php` adiciona as permissões e associa-as a `admin` e `manager`.

---

## Stack

| Tecnologia     | Uso                                                |
| -------------- | -------------------------------------------------- |
| PHP ≥ 8.1      | Runtime                                            |
| Hyperf ~3.1    | HTTP server, DI, DB, Redis, validação, comandos    |
| Swoole / Swow  | Motor de corrutinas (ambiente Hyperf)              |
| MySQL 8.x      | Persistência opcional                              |
| Redis 7        | Opcional: tokens de reset em produção multi-worker |
| Symfony Mailer | Envio de e-mail (ex.: Mailpit)                     |
| Pest / PHPUnit | Testes                                             |
| PHPStan        | Análise estática (opcional)                        |

---

## Requisitos

### Com Docker (recomendado)

- Docker e Docker Compose
- Não é obrigatório ter PHP ou Swoole instalados no host para **correr a aplicação** dentro do container

### Sem Docker

- Linux ou macOS (ou WSL2 no Windows)
- PHP ≥ 8.1 com extensões adequadas ao Hyperf (**Swoole** ≥ 5 ou **Swow** ≥ 1.3, JSON, Pcntl, OpenSSL, PDO MySQL se usar DB, Redis se usar cliente Redis)
- Composer

Detalhes oficiais: [documentação Hyperf](https://hyperf.wiki).

---

## Início rápido com Docker

1. **Clonar / entrar no projeto**

   ```bash
   cd hyperf-skeleton
   ```

2. **Ambiente**

   ```bash
   cp .env.example .env
   ```

   Ajuste `.env` se necessário (nome da base, `APP_USER_REPOSITORY=db` para MySQL, segredos).

3. **Subir serviços**

   ```bash
   docker compose build
   docker compose up -d
   ```

4. **Migrações** (se `APP_USER_REPOSITORY=db`)

   Com os containers em execução (`docker compose up -d`):

   ```bash
   docker compose exec hyperf-skeleton php bin/hyperf.php migrate
   ```

   Sem subir o serviço HTTP, pode usar um contentor efémero:

   ```bash
   docker compose run --rm --entrypoint php hyperf-skeleton /opt/www/bin/hyperf.php migrate
   ```

5. **Verificar a API**

   ```bash
   curl -s http://127.0.0.1:9501/api/v1/
   ```

   Resposta esperada: JSON com `method` e `message` (ex.: `Hello Hyperf.`).

**Porta da API:** `9501` (mapeada em `docker-compose.yml`).

---

## Desenvolvimento local (sem Docker)

```bash
composer install
cp .env.example .env
# Configurar DB_HOST=127.0.0.1, Redis, etc., conforme o seu ambiente
php bin/hyperf.php migrate   # se usar MySQL
php bin/hyperf.php start
```

Servidor por omissão escuta na porta definida pela configuração Hyperf (frequentemente `9501`).

---

## Variáveis de ambiente

O ficheiro **[.env.example](.env.example)** é a referência completa. Resumo:

| Variável                   | Descrição                                                                                                        |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| `APP_ENV`                  | Ambiente (`dev`, `testing`, …)                                                                                   |
| `APP_DEBUG`                | Se `true`, erros 500 em JSON podem incluir trace (desligar em produção)                                          |
| `APP_LOCALE`               | Idioma da API (`pt_BR` por omissão; ficheiros em `storage/languages/{locale}/`). Nos testes PHPUnit usa-se `en`. |
| `APP_FALLBACK_LOCALE`      | Idioma de recurso se faltar chave no locale principal (`en` por omissão)                                         |
| `APP_USER_REPOSITORY`      | `memory` ou `db`                                                                                                 |
| `APP_AUTH_SECRET`          | Segredo HMAC para tokens Bearer (obrigatório trocar em produção)                                                 |
| `APP_AUTH_TOKEN_TTL`       | TTL do access token em segundos                                                                                  |
| `APP_AUTH_RESET_STORE`     | `array` (dev) ou `redis`                                                                                         |
| `APP_AUTH_RESET_TOKEN_TTL` | TTL do código de reset                                                                                           |
| `DB_*`                     | Ligação MySQL quando `APP_USER_REPOSITORY=db`                                                                    |
| `REDIS_*`                  | Redis (reset store e outros usos)                                                                                |
| `MAIL_*` / `MAILER_DSN`    | E-mail (ex.: `MAIL_HOST=mailpit`, `MAIL_PORT=1025` no Docker)                                                    |

---

## Base de dados e migrações

Migrações em `migrations/` (tabela `users`, RBAC e contas de _staff_ de desenvolvimento).

```bash
php bin/hyperf.php migrate
php bin/hyperf.php migrate:status
```

Garanta que `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` coincidem com o MySQL (no Docker, `MYSQL_ROOT_PASSWORD` e `MYSQL_DATABASE` em `docker-compose.yml`).

**Contas seed (após `migrate` com `APP_USER_REPOSITORY=db`):** `admin@victordev.com` e `manager@victordev.com`, palavra-passe **`VictorDev123!`**. Não use estas credenciais em produção.

---

## Serviços auxiliares (compose)

| Serviço           | Portas (host)              | Função                                |
| ----------------- | -------------------------- | ------------------------------------- |
| `hyperf-skeleton` | `9501`                     | API Hyperf                            |
| `mysql`           | `3306`                     | MySQL 8.4                             |
| `redis`           | `6379`                     | Redis                                 |
| `mailpit`         | `8025` (UI), `1025` (SMTP) | Captura de e-mails em desenvolvimento |

---

## Comandos úteis

```bash
# Servidor
php bin/hyperf.php start

# Migrações
php bin/hyperf.php migrate

# Testes (host sem Swoole: testes de integração HTTP podem ser ignorados)
./vendor/bin/pest --no-coverage

# Testes no Docker (com Swoole — suíte completa)
docker compose run --rm --entrypoint php hyperf-skeleton /opt/www/vendor/bin/pest --no-coverage

# PHPStan (exemplo)
./vendor/bin/phpstan analyse app -c phpstan.neon.dist
```

---

## Testes e análise estática

- **Pest** — Configuração em `phpunit.xml.dist`, bootstrap em `test/bootstrap.php`.
- **Unitários** — `test/Unit/` (ex.: fluxos de autenticação e aplicação).
- **Integração HTTP** — `test/Cases/` com `Hyperf\Testing\TestCase`; requer **ext-swoole** no PHP que executa os testes (por exemplo imagem `dev.Dockerfile`).

Sem Swoole no host, o exemplo de integração pode aparecer como _skipped_; no container Hyperf deve passar na íntegra.

---

## Documentação

| Documento                          | Conteúdo                                                                      |
| ---------------------------------- | ----------------------------------------------------------------------------- |
| [docs/API.md](docs/API.md)         | Rotas `/api`, corpos, validações, códigos HTTP, exemplos `curl`, autenticação |
| [docs/PROJECT.md](docs/PROJECT.md) | Camadas DDD/hexagonal, regras de dependência, convenções, fluxo de pedidos    |

---

## Estrutura do repositório

```
app/
  Application/     # Casos de uso (handlers, commands, queries, portas)
  Domain/          # Entidades, VOs, repositórios (interfaces), eventos
  Infrastructure/  # DB, Redis, mail, tokens, implementações das portas
  Controller/      # Entrada HTTP
  Http/Request/    # Validação (FormRequest)
  Middleware/
  Exception/Handler/
config/            # Rotas, autoload, serviços
migrations/
test/              # Pest / PHPUnit
docs/              # API + arquitectura
```

Descrição detalhada: [docs/PROJECT.md](docs/PROJECT.md).

---

## Licença

Este projeto base segue a licença indicada em [LICENSE](LICENSE) e em [composer.json](composer.json) (Apache-2.0 no _skeleton_ oficial Hyperf).
