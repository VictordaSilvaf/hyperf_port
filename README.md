# Hyperf API — DDD / Hexagonal

API REST em **[Hyperf 3.x](https://hyperf.io)** com organização em camadas (**Domain**, **Application**, **Infrastructure**), autenticação por **Bearer token** assinado (HMAC), registo/login, reset de palavra-passe com código por e-mail (Mailpit em desenvolvimento), validação JSON e testes com **Pest**.

O fluxo de desenvolvimento **recomendado** é **100% via Docker**, usando a CLI **[hyper](hyper)** (estilo Laravel Sail). No host só precisa de **Docker**, **Docker Compose** e **Git** — PHP/Swoole correm dentro do container.

---

## Conteúdo

- [Funcionalidades](#funcionalidades)
- [Stack](#stack)
- [Requisitos](#requisitos)
- [Início rápido (Docker + hyper)](#início-rápido-docker--hyper)
- [CLI Hyper](#cli-hyper)
- [Desenvolvimento no container](#desenvolvimento-no-container)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [Base de dados e migrações](#base-de-dados-e-migrações)
- [Serviços auxiliares (compose)](#serviços-auxiliares-compose)
- [Testes e qualidade](#testes-e-qualidade)
- [Git Flow, commits e hooks](#git-flow-commits-e-hooks)
- [Desenvolvimento local (sem Docker)](#desenvolvimento-local-sem-docker)
- [Documentação](#documentação)
- [Estrutura do repositório](#estrutura-do-repositório)
- [Licença](#licença)

---

## Funcionalidades

- **HTTP** — Prefixo global `/api/v1`; respostas em JSON; erros de validação e HTTP tratados de forma previsível (`APP_DEBUG` controla detalhe em 500).
- **Health** — `GET /api/v1/health/live` (liveness), `/health/ready` e `/health` (readiness); verifica DB/Redis conforme `.env`.
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
| PHP ≥ 8.4      | Runtime (container `hyperf-skeleton`)              |
| Hyperf ~3.1    | HTTP server, DI, DB, Redis, validação, comandos    |
| Swoole / Swow  | Motor de corrutinas (ambiente Hyperf)              |
| MySQL 8.x      | Persistência opcional                              |
| Redis 7        | Opcional: tokens de reset em produção multi-worker |
| Symfony Mailer | Envio de e-mail (ex.: Mailpit)                     |
| Pest / PHPUnit | Testes                                             |
| PHPStan        | Análise estática                                   |
| Docker Compose | Ambiente de desenvolvimento                        |

---

## Requisitos

### Desenvolvimento (recomendado)

| Ferramenta      | Obrigatório | Notas                                      |
| --------------- | ----------- | ------------------------------------------ |
| Docker          | Sim         | Engine recente                             |
| Docker Compose  | Sim         | v2 (`docker compose`)                      |
| Git             | Sim         | Hooks e Git Flow                           |
| [direnv](https://direnv.net) | Recomendado | Permite `hyper` sem `./` ou caminho        |
| PHP no host     | Opcional    | Só para Git hooks (`pre-push`) sem Docker  |

**Não precisa** de PHP, Swoole ou Composer instalados no host para correr a API, migrações, testes ou qualidade — tudo passa pelo container via `hyper`.

### Sem Docker (avançado)

Linux/macOS/WSL2, PHP ≥ 8.4 com Swoole ≥ 5 (ou Swow), Composer. Ver [Desenvolvimento local](#desenvolvimento-local-sem-docker).

---

## Início rápido (Docker + hyper)

### 1. Clonar e configurar ambiente

```bash
git clone <repo-url> hyperf_p && cd hyperf_p
cp .env.example .env
chmod +x hyper
```

Edite `.env` se necessário. Para MySQL + RBAC + seeds de dev:

```bash
# .env
APP_USER_REPOSITORY=db
```

Ajuste `UID`/`GID` no `.env` ao seu utilizador Linux (`id -u` / `id -g`) para permissões correctas no volume montado.

### 2. Instalar e subir (um comando)

```bash
./hyper install
```

O `install` faz, **dentro do Docker**:

1. `docker compose build`
2. `composer install` → popula `vendor/` no host (volume montado)
3. `docker compose up -d` → API, MySQL, Redis, Mailpit
4. `migrate` → esquema e seeds (se aplicável)

### 3. Activar CLI `hyper` sem `./`

Depois do passo 2 existe `vendor/bin/hyper`. Escolha **uma** opção:

**direnv (recomendado)**

```bash
direnv allow
hyper help
```

**Ou** função no shell (`~/.zshrc` / `~/.bashrc`):

```bash
eval "$(cat "$(git rev-parse --show-toplevel)/scripts/hyper-shell.sh")"
```

**Ou** caminho explícito: `vendor/bin/hyper help`

### 4. Verificar a API

```bash
curl -s http://127.0.0.1:${API_PORT:-9501}/api/v1/health/live
curl -s http://127.0.0.1:${API_PORT:-9501}/api/v1/health/ready
curl -s http://127.0.0.1:${API_PORT:-9501}/api/v1/
```

Resposta esperada na raiz: JSON com `method` e `message` (ex.: `Hello Hyperf.`).

### 5. Git hooks (opcional, uma vez por clone)

Os hooks correm **no host** (Git), mas as dependências PHP vêm do `vendor/` criado pelo container:

```bash
composer setup:hooks   # ou: git config core.hooksPath .githooks
```

Para qualidade completa no `pre-push`, instale `php-xml` no host **ou** confie no CI — a suíte Pest completa usa `hyper test` no container.

### Resumo do fluxo diário

```bash
hyper up -d          # subir serviços
hyper migrate        # após novas migrações
hyper restart        # após alterar rotas, config ou .env
hyper test           # testes
hyper quality        # lint + PHPStan + Pest
hyper down           # parar
```

---

## CLI Hyper

Script **[hyper](hyper)** na raiz — encapsula `docker compose exec` / `run`, como o `vendor/bin/sail` do Laravel.

| Momento | Comando |
| ------- | ------- |
| Primeira vez (sem `vendor/`) | `./hyper install` ou `./hyper bootstrap` |
| Depois de `composer install` | `hyper …` (via direnv ou `vendor/bin/hyper`) |

### Setup da CLI

| Opção | Como activar | Uso |
| ----- | ------------ | --- |
| **direnv** | `direnv allow` (lê [.envrc](.envrc)) | `hyper migrate` |
| **Função shell** | `eval "$(cat …/scripts/hyper-shell.sh)"` | `hyper` de subpastas |
| **Caminho explícito** | — | `vendor/bin/hyper migrate` |
| **Bootstrap** | `./hyper` na raiz | Sempre funciona, mesmo sem `vendor/` |

### Comandos

| Comando | Descrição |
| ------- | --------- |
| `hyper install` | build + composer install + up -d + migrate |
| `hyper bootstrap` | Só `composer install` no container |
| `hyper up -d` / `down` / `build` / `ps` | Docker Compose |
| `hyper restart` | Reinicia API (recarrega rotas/config) |
| `hyper logs -f` | Logs da API |
| `hyper shell` | Shell no container |
| `hyper migrate` | Migrações |
| `hyper migrate:status` | Estado das migrações |
| `hyper routes` | Lista rotas |
| `hyper test` | Pest (Swoole — suíte completa) |
| `hyper lint` / `lint:fix` / `analyse` / `quality` | Qualidade no container |
| `hyper mysql` / `redis` | Clientes dos serviços |
| `hyper composer …` | Composer no container |
| `hyper php …` | PHP no container |
| `hyper hyperf …` | `bin/hyperf.php` (alias `hf`, `artisan`) |

Comandos desconhecidos vão para `bin/hyperf.php` (ex.: `hyper gen:model User`).

Variável opcional: `HYPER_SERVICE=hyperf-skeleton`.

---

## Desenvolvimento no container

Tudo abaixo assume containers a correr (`hyper up -d`).

### Alterar código

O volume `./:/opt/www` sincroniza o código em tempo real. A API Hyperf **não** recarrega rotas/DI automaticamente:

```bash
hyper restart    # após mudar config/routes/dependencies.php
```

### Dependências PHP

```bash
hyper composer require pacote/nome
hyper composer update
```

### Base de dados

```bash
hyper migrate
hyper migrate:status
hyper mysql        # cliente interactivo
```

### E-mail (dev)

Mailpit UI: [http://127.0.0.1:8025](http://127.0.0.1:8025) (porta `MAIL_WEB_PORT` no `.env`).

### Depurar

```bash
hyper logs -f
hyper shell
hyper exec php bin/hyperf.php describe:routes
```

### Após clonar noutra máquina

```bash
cp .env.example .env   # ou copie o seu .env
./hyper install
direnv allow           # se usar direnv
```

---

## Variáveis de ambiente

Referência completa: **[.env.example](.env.example)**.

| Variável                   | Descrição                                                                                                        |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| `APP_ENV`                  | Ambiente (`dev`, `testing`, …)                                                                                   |
| `APP_DEBUG`                | Se `true`, erros 500 em JSON podem incluir trace (desligar em produção)                                          |
| `APP_LOCALE`               | Idioma da API (`pt_BR` por omissão; ficheiros em `storage/languages/{locale}/`). Nos testes PHPUnit usa-se `en`. |
| `APP_FALLBACK_LOCALE`      | Idioma de recurso se faltar chave no locale principal (`en` por omissão)                                         |
| `APP_USER_REPOSITORY`      | `memory` ou `db` — use `db` com Docker para MySQL + RBAC                                                         |
| `APP_AUTH_SECRET`          | Segredo HMAC para tokens Bearer (obrigatório trocar em produção)                                                 |
| `APP_AUTH_TOKEN_TTL`       | TTL do access token em segundos                                                                                  |
| `APP_AUTH_RESET_STORE`     | `array` (dev) ou `redis`                                                                                         |
| `APP_AUTH_RESET_TOKEN_TTL` | TTL do código de reset                                                                                           |
| `DB_*`                     | Ligação MySQL — no Docker use `DB_HOST=mysql`                                                                    |
| `REDIS_*`                  | Redis — no Docker use `REDIS_HOST=redis`                                                                         |
| `MAIL_*` / `MAILER_DSN`    | E-mail (`MAIL_HOST=mailpit`, `MAIL_PORT=1025`)                                                                   |
| `API_PORT`                 | Porta da API no host (omissão `9501`)                                                                            |
| `UID` / `GID`              | Utilizador no container (permissões do volume)                                                                   |

### `.env` e Docker Compose

| Camada | O que lê o `.env` |
|--------|-------------------|
| **Hyperf** (app) | `config/autoload/*.php` — hosts **internos** da rede Docker (`mysql`, `redis`, `mailpit`) |
| **Docker Compose** | `env_file` + substituição de portas publicadas (`API_PORT`, `DB_PUBLISH_PORT`, …) |

**Importante:**

- `REDIS_PORT` / `DB_PORT` = portas **dentro** da rede Docker (`6379` / `3306`).
- Para mudar portas no **host**, use `REDIS_PUBLISH_PORT`, `DB_PUBLISH_PORT`, etc.
- Após alterar `.env`: `hyper up -d` e `hyper restart`.

---

## Base de dados e migrações

Migrações em `migrations/` (tabela `users`, RBAC e contas de _staff_ de desenvolvimento).

```bash
hyper migrate
hyper migrate:status
```

`DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env` devem coincidir com o serviço `mysql` no compose (`DB_PASSWORD` → `MYSQL_ROOT_PASSWORD`).

**Contas seed** (após `migrate` com `APP_USER_REPOSITORY=db`): `admin@victordev.com` e `manager@victordev.com`, palavra-passe **`VictorDev123!`**. Não use em produção.

---

## Serviços auxiliares (compose)

| Serviço           | Porta no host (omissão)    | Função                                |
| ----------------- | -------------------------- | ------------------------------------- |
| `hyperf-skeleton` | `9501` (`API_PORT`)        | API Hyperf                            |
| `mysql`           | `3306` (`DB_PUBLISH_PORT`) | MySQL 8.4                             |
| `redis`           | `6379` (`REDIS_PUBLISH_PORT`) | Redis                              |
| `mailpit`         | `8025` / `1025`            | UI web + SMTP (dev)                   |

---

## Testes e qualidade

Execute **no container** para Swoole e suíte completa:

```bash
hyper test
hyper lint
hyper lint:fix
hyper analyse
hyper quality        # lint + analyse + test
```

- **Pest** — `phpunit.xml.dist`, bootstrap em `test/bootstrap.php`.
- **Unitários** — `test/Unit/`.
- **Integração HTTP** — `test/Cases/`; requer Swoole (container).

No host, sem Swoole, testes de integração podem ser _skipped_; use `hyper test` para validação completa antes de PR.

---

## Git Flow, commits e hooks

Git Flow, **Conventional Commits** e hooks em `.githooks/`.

**Setup (uma vez por clone):**

```bash
./hyper install          # ou ./hyper bootstrap — cria vendor/
composer setup:hooks
```

| Hook | Validação |
|------|-----------|
| `pre-commit` | PHP-CS-Fixer nos ficheiros staged |
| `prepare-commit-msg` | Auto-formata mensagens (ex.: Cursor ✨) |
| `commit-msg` | Conventional Commits |
| `pre-push` | Branch Git Flow + lint + PHPStan + Pest (host) |

**Antes de PR:** `hyper quality` (container) + commits conforme [CONTRIBUTING.md](CONTRIBUTING.md).

O Cursor usa **`.cursorrules`** para mensagens de commit; o hook `prepare-commit-msg` corrige formato inválido.

---

## Desenvolvimento local (sem Docker)

Apenas se precisar correr PHP/Swoole directamente no host:

```bash
composer install
cp .env.example .env
# DB_HOST=127.0.0.1, REDIS_HOST=127.0.0.1, etc.
php bin/hyperf.php migrate
php bin/hyperf.php start
composer quality
```

Detalhes: [documentação Hyperf](https://hyperf.wiki).

---

## Documentação

| Documento                          | Conteúdo                                                                      |
| ---------------------------------- | ----------------------------------------------------------------------------- |
| [docs/API.md](docs/API.md)         | Rotas `/api`, corpos, validações, códigos HTTP, exemplos `curl`, autenticação |
| [docs/PROJECT.md](docs/PROJECT.md) | Camadas DDD/hexagonal, regras de dependência, convenções, fluxo de pedidos    |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Git Flow, commits, hooks, checklist de PR                                     |

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
hyper              # CLI Docker (estilo Sail)
.envrc             # direnv: PATH_add vendor/bin
```

Descrição detalhada: [docs/PROJECT.md](docs/PROJECT.md).

---

## Licença

Este projeto base segue a licença indicada em [LICENSE](LICENSE) e em [composer.json](composer.json) (Apache-2.0 no _skeleton_ oficial Hyperf).
