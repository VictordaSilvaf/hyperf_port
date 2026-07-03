# Hyperf API — DDD / Hexagonal

API REST em **[Hyperf 3.x](https://hyperf.io)** com organização em camadas (**Domain**, **Application**, **Infrastructure**, **Presentation**), autenticação por **Bearer token** assinado (HMAC), registo/login, reset de palavra-passe com código por e-mail (Mailpit em desenvolvimento), **portfólio de projetos** (admin + público), **upload de imagens com processamento assíncrono**, validação JSON e testes com **Pest**.

O fluxo de desenvolvimento **recomendado** é **100% via Docker**, usando a CLI **[hyper](hyper)** (estilo Laravel Sail). No host só precisa de **Docker**, **Docker Compose** e **Git** — PHP/Swoole correm dentro do container.

---

## Conteúdo

- [Funcionalidades](#funcionalidades)
- [Portfólio e projetos](#portfólio-e-projetos)
- [Page Builder e site](#page-builder-e-site)
- [Upload e processamento de imagens](#upload-e-processamento-de-imagens)
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
- **Persistência** — Repositório de utilizadores em **memória** ou **PostgreSQL** (`APP_USER_REPOSITORY`), configurável por `.env`.
- **RBAC** — Papéis `admin`, `manager`, `user` (seed na migração); permissões granulares; criação de novos papéis; atribuição de permissões a papéis e de papéis a utilizadores (`/api/v1/admin/...`). Após `migrate`, todos os utilizadores existentes recebem o papel `user`. São criados dois utilizadores de desenvolvimento (com PostgreSQL): `admin@victordev.com` (papel `admin`) e `manager@victordev.com` (papel `manager`), ambos com palavra-passe inicial **`VictorDev123!`** — altere ou elimine em produção.
- **Admin — utilizadores** — Listagem, detalhe, criação e edição em `/api/v1/admin/users` (permissões `users.view`, `users.create`, `users.update`; ver `docs/API.md`). Migração `2026_05_23_000000_add_users_create_update_permissions.php` adiciona as permissões e associa-as a `admin` e `manager`.
- **Portfólio — projetos** — CRUD admin, publicar/arquivar/rascunho, soft/force delete, duplicar, estatísticas, reordenação, sync de taxonomias e galeria de imagens. API pública: listagem, detalhe por slug, relacionados, busca e taxonomias (`categories`, `technologies`, `tags`). Ver [Portfólio e projetos](#portfólio-e-projetos) e [docs/ROUTES.md](docs/ROUTES.md).
- **Page Builder — páginas** — CRUD admin, blocos compostos (hero, markdown, galeria, listagens de projectos, etc.), publicar/arquivar/rascunho, reordenação e duplicar. API pública: home (`GET /pages/home`), listagem, detalhe por slug e catálogo de tipos de bloco (`GET /block-types`). Ver [Page Builder e site](#page-builder-e-site) e [docs/ROUTES.md](docs/ROUTES.md).
- **Site Settings** — Configurações globais (nav, footer, social, branding, SEO defaults). Leitura pública em `GET /site/settings`; edição admin com permissão `site.update`.
- **Upload de ficheiros** — `POST /api/v1/admin/uploads` (MinIO/R2). Imagens passam por fila Redis: optimização, WebP e thumbnail. Ver [Upload e processamento de imagens](#upload-e-processamento-de-imagens).
- **Cache e métricas (projetos)** — Cache Redis para listagens/detalhe público; contador de views em Redis com flush assíncrono para PostgreSQL (`FlushProjectViewsJob`).
- **Busca avançada (PostgreSQL)** — Full-text search (`tsvector`, config `portuguese`) + trigram (`pg_trgm`) em projetos e utilizadores; índices GIN via migração. Substitui `ILIKE` puro em produção com DB.

---

## Portfólio e projetos

Contexto DDD em `app/Domain/Project/` (+ `Category`, `Technology`, `Tag`, `Upload`, `ProjectImage`).

| Área | Endpoints (prefixo `/api/v1`) | Notas |
| ---- | ----------------------------- | ----- |
| **Público** | `GET /projects`, `/projects/{slug}`, `/projects/{slug}/related`, `/search`, `/categories`, `/technologies`, `/tags` | Só projetos `published`; detalhe incrementa views (`trackView`) |
| **Admin** | `GET/POST/PATCH/DELETE /admin/projects`, publish/archive/draft, duplicate, statistics, imagens, sync taxonomias | RBAC `projects.*`, `uploads.create` |

**Estado do projecto:** `draft` → `published` → `archived`; soft delete com restore; slug único (UUID v4).

**Resposta admin:** `{ "data": ProjectDetail }` — título, slug, conteúdo Markdown, URLs, thumbnail/cover, taxonomias, galeria, views, `published_at`, `featured`.

**Documentação completa:** [docs/ROUTES.md](docs/ROUTES.md) — todos os bodies JSON, query params e permissões.

---

## Page Builder e site

Contexto DDD em `app/Domain/Page/` (+ blocos validados em `Domain/Page/Block/`) e `app/Domain/Site/`.

| Área | Endpoints (prefixo `/api/v1`) | Notas |
| ---- | ----------------------------- | ----- |
| **Público — páginas** | `GET /pages/home`, `/pages`, `/pages/{slug}`, `/block-types` | Só páginas `published`; home = página com `is_home: true`; blocos enriquecidos (URLs de upload, projectos, taxonomias) |
| **Público — site** | `GET /site/settings` | Nav, footer, social, branding e defaults SEO; cache Redis (TTL 300s) |
| **Admin — páginas** | `GET/POST/PATCH/DELETE /admin/pages`, publish/archive/draft, duplicate, reorder, `PUT /admin/pages/{id}/blocks` | RBAC `pages.*` |
| **Admin — site** | `GET/PUT /admin/site/settings` | RBAC `site.update` |

**Estado da página:** `draft` → `published` → `archived`; soft delete com restore; slug único; uma única página pode ser `is_home`.

**Resposta admin:** `{ "data": PageDetail }` — título, slug, layout, blocos, SEO, `published_at`, `order`.

**Documentação completa:** [docs/ROUTES.md](docs/ROUTES.md) — schemas `PageDetail`, `PageBlock`, `SiteSettings` e payloads de blocos.

---

## Upload e processamento de imagens

Pipeline assíncrono para ficheiros `image/*`:

```
Upload → Queue (Redis) → Optimize → WebP → Thumbnail → Save
```

| Etapa | Descrição |
| ----- | --------- |
| **Upload** | `StoreUploadHandler` grava original em `uploads/YYYY/MM/` no bucket (`victorsf/{prefix}/`) |
| **Queue** | `ProcessUploadJob` na fila Hyperf (`async_queue`, driver Redis) |
| **Optimize** | Redimensiona (max configurável), corrige EXIF, recompressão JPEG/PNG |
| **WebP** | Variante `.webp` para entrega web |
| **Thumbnail** | `_thumb.webp` (cover crop, 400×400 por omissão) |
| **Save** | Actualiza tabela `uploads`: `processing_status`, paths/URLs, `width`/`height` |

**Resposta do upload** inclui `processing_status`, `display_url` (WebP quando pronto) e `thumbnail_url`.

**Modos de execução:**

| `APP_USER_REPOSITORY` | `UPLOAD_QUEUE_PROCESSING` | Comportamento |
| --------------------- | ------------------------- | ------------- |
| `db` | `true` (omissão) | Fila Redis — worker `AsyncQueueConsumer` no `hyperf.php start` |
| `memory` / testes | — | Processamento **síncrono** (`SyncUploadJobDispatcher`) |

**Requisito:** extensão PHP **GD** com WebP (incluída nas imagens Docker `Dockerfile` / `dev.Dockerfile`).

**Variáveis** (ver [.env.example](.env.example)):

| Variável | Omissão | Descrição |
| -------- | ------- | --------- |
| `UPLOAD_QUEUE_PROCESSING` | `true` | Enfileirar processamento de imagens |
| `UPLOAD_IMAGE_MAX_WIDTH` / `MAX_HEIGHT` | `2048` | Limite de redimensionamento |
| `UPLOAD_JPEG_QUALITY` | `85` | Qualidade JPEG optimizado |
| `UPLOAD_WEBP_QUALITY` | `82` | Qualidade WebP |
| `UPLOAD_THUMB_WIDTH` / `THUMB_HEIGHT` | `400` | Dimensões do thumbnail |

**Galeria de projectos:** `AddProjectImageHandler` liga `upload_id` ao projecto; `SetProjectThumbnail`/`setCover` preferem paths WebP/thumbnail quando disponíveis.

---

## Stack

| Tecnologia     | Uso                                                |
| -------------- | -------------------------------------------------- |
| PHP ≥ 8.4      | Runtime (container `hyperf-skeleton`); **ext-gd** para imagens |
| Hyperf ~3.1    | HTTP server, DI, DB, Redis, validação, async queue, comandos |
| Swoole / Swow  | Motor de corrutinas (ambiente Hyperf)              |
| PostgreSQL 16  | Persistência; **pg_trgm** + **tsvector** para busca |
| Redis 7        | Reset tokens, cache público de projetos, views, fila async |
| MinIO / R2     | Object storage (uploads, variantes WebP/thumb)     |
| Symfony Mailer | Envio de e-mail (ex.: Mailpit)                     |
| Pest / PHPUnit | Testes (54+ casos, suíte no container)             |
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

Edite `.env` se necessário. Para PostgreSQL + RBAC + seeds de dev:

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
3. `docker compose up -d` → API, PostgreSQL, Redis, Mailpit, MinIO
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
| `hyper postgres` / `psql` | Cliente PostgreSQL |
| `hyper pgadmin` | URL e credenciais do pgAdmin |
| `hyper redis` | `redis-cli` |
| `hyper minio` | Info MinIO (dev) |
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
hyper psql           # cliente interactivo PostgreSQL
hyper pgadmin        # URL do pgAdmin (UI web)
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
| `APP_USER_REPOSITORY`      | `memory` ou `db` — use `db` com Docker para PostgreSQL + RBAC                                                    |
| `APP_AUTH_SECRET`          | Segredo HMAC para tokens Bearer (obrigatório trocar em produção)                                                 |
| `APP_AUTH_TOKEN_TTL`       | TTL do access token em segundos                                                                                  |
| `APP_AUTH_RESET_STORE`     | `array` (dev) ou `redis`                                                                                         |
| `APP_AUTH_RESET_TOKEN_TTL` | TTL do código de reset                                                                                           |
| `DB_*`                     | Ligação PostgreSQL — no Docker use `DB_HOST=postgres`, `DB_PORT=5432`                                          |
| `REDIS_*`                  | Redis — no Docker use `REDIS_HOST=redis`                                                                         |
| `MAIL_*` / `MAILER_DSN`    | E-mail (`MAIL_HOST=mailpit`, `MAIL_PORT=1025`)                                                                   |
| `FILESYSTEM_DRIVER`        | `minio` (dev) ou `r2` (Cloudflare produção)                                                                    |
| `FILESYSTEM_PREFIX`        | `development` (dev) ou `production` (R2 prod) — pasta dentro do bucket `victorsf`                              |
| `R2_*` / `MINIO_*`         | Credenciais e endpoint do object storage                                                                         |
| `UPLOAD_*`                 | Processamento de imagens (fila, qualidade, dimensões) — ver [Upload](#upload-e-processamento-de-imagens)          |
| `API_PORT`                 | Porta da API no host (omissão `9501`)                                                                            |
| `UID` / `GID`              | Utilizador no container (permissões do volume)                                                                   |

### `.env` e Docker Compose

| Camada | O que lê o `.env` |
|--------|-------------------|
| **Hyperf** (app) | `config/autoload/*.php` — hosts **internos** da rede Docker (`postgres`, `redis`, `mailpit`, `minio`) |
| **Docker Compose** | `env_file` + substituição de portas publicadas (`API_PORT`, `DB_PUBLISH_PORT`, …) |

**Importante:**

- `REDIS_PORT` / `DB_PORT` = portas **dentro** da rede Docker (`6379` / `5432`).
- Para mudar portas no **host**, use `REDIS_PUBLISH_PORT`, `DB_PUBLISH_PORT`, etc.
- Após alterar `.env`: `hyper up -d` e `hyper restart`.

---

## Base de dados e migrações

**PostgreSQL 16** via `hyperf/database-pgsql`. Migrações em `migrations/` (utilizadores, RBAC, taxonomias, projetos, uploads, índices de busca).

Principais migrações recentes:

| Migração | Conteúdo |
| -------- | -------- |
| `2026_07_04_*` | Taxonomias, extensão `projects`, relações, seeds, permissões upload |
| `2026_07_05_000000_*` | Colunas de processamento em `uploads` |
| `2026_07_05_000001_*` | `pg_trgm`, `search_vector` (GIN) em `projects` e trigram em `users` |

```bash
hyper migrate
hyper migrate:status
hyper psql           # cliente interactivo
```

`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` e `DB_CHARSET=utf8` no `.env` devem coincidir com o serviço `postgres` no compose. **Não use** `utf8mb4` — PostgreSQL usa `utf8`.

Se actualizou de MySQL: copie a secção `DB_*` de `.env.example`, remova o volume antigo (`docker compose down -v` ou apague `mysql-data`) e corra `hyper up -d` + `hyper migrate`.

**pgAdmin (dev):** [http://127.0.0.1:5050](http://127.0.0.1:5050) — login `PGADMIN_DEFAULT_EMAIL` / `PGADMIN_DEFAULT_PASSWORD`. O servidor **Hyperf (Docker)** já vem pré-configurado; use `DB_PASSWORD` ao ligar ao Postgres.

**Contas seed** (após `migrate` com `APP_USER_REPOSITORY=db`): `admin@victordev.com` e `manager@victordev.com`, palavra-passe **`VictorDev123!`**. Não use em produção.

---

## Serviços auxiliares (compose)

| Serviço           | Porta no host (omissão)    | Função                                |
| ----------------- | -------------------------- | ------------------------------------- |
| `hyperf-skeleton` | `9501` (`API_PORT`)        | API Hyperf                            |
| `postgres`        | `5432` (`DB_PUBLISH_PORT`) | PostgreSQL 16                         |
| `pgadmin`         | `5050` (`PGADMIN_PUBLISH_PORT`) | UI web para PostgreSQL (dev)     |
| `redis`           | `6379` (`REDIS_PUBLISH_PORT`) | Redis (cache, views, fila async)     |
| `mailpit`         | `8025` / `1025`            | UI web + SMTP (dev)                   |
| `minio`           | `9000` / `9001`            | Object storage S3 (dev/testes)        |

---

## Object storage (R2 / MinIO)

Armazenamento de ficheiros via **S3-compatible API** (`hyperf/filesystem` + Flysystem).

| Ambiente | Driver | Prefixo | Caminho no bucket |
| -------- | ------ | ------- | ----------------- |
| **Dev / testes (Docker)** | `minio` | `development` | `victorsf/development/` |
| **Produção (R2)** | `r2` | `production` | `victorsf/production/` |

Estrutura partilhada no bucket **`victorsf`**:

```
victorsf/
├── development/    ← MinIO (dev, testes)
└── production/     ← Cloudflare R2 (prod)
```

### Variáveis (`.env`)

```bash
FILESYSTEM_DRIVER=minio          # dev: minio | produção: r2
FILESYSTEM_PREFIX=development    # dev: development | R2 prod: production

# Cloudflare R2 (produção)
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=victorsf
R2_ENDPOINT=https://b702d6c53f8aed6df35d42c2db93fb89.r2.cloudflarestorage.com
R2_REGION=auto
R2_PUBLIC_URL=

# MinIO (dev / testes)
MINIO_ACCESS_KEY_ID=minioadmin
MINIO_SECRET_ACCESS_KEY=minioadmin
MINIO_BUCKET=victorsf
MINIO_ENDPOINT=http://minio:9000
MINIO_PUBLIC_URL=http://127.0.0.1:9000/victorsf
# URLs públicas ficam: {MINIO_PUBLIC_URL}/{FILESYSTEM_PREFIX}/uploads/...
```

**Produção R2:** `FILESYSTEM_DRIVER=r2`, `FILESYSTEM_PREFIX=production` + credenciais R2. Nunca commite secrets.

**Dev:** MinIO console em [http://127.0.0.1:9001](http://127.0.0.1:9001) (user/pass = `MINIO_ACCESS_KEY_ID` / `MINIO_SECRET_ACCESS_KEY`).

### Upload via API

`POST /api/v1/admin/uploads` — `multipart/form-data`, campo `file`, permissão `uploads.create`. Resposta inclui `id`, `path`, `url`, `processing_status`, `display_url`, `thumbnail_url`.

### Processamento assíncrono

Com `APP_USER_REPOSITORY=db` e containers a correr, o consumer da fila processa jobs automaticamente (`hyperf.php start`). Após alterar código de imagens ou subir nova imagem Docker com GD:

```bash
hyper build
hyper up -d
hyper restart
```

### Uso no código

Injete a porta `App\Application\Storage\ObjectStorageInterface`:

```php
public function __construct(private ObjectStorageInterface $storage) {}

$this->storage->write('uploads/file.txt', $contents);
// Grava em victorsf/development/uploads/file.txt (ou production/ em prod)
$url = $this->storage->publicUrl('uploads/file.txt');
```

### Health check

Com `FILESYSTEM_DRIVER=minio` ou `r2`, `/api/v1/health/ready` inclui probe `storage` (write/read/delete de teste). Para torná-lo obrigatório na readiness: `APP_STORAGE_HEALTH_REQUIRED=true`.

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
- **Unitários** — `test/Unit/` — Auth, Users, RBAC, **Project** (CRUD, views, imagens, taxonomias), **Upload** (pipeline de imagens).
- **Integração HTTP** — `test/Cases/`; requer Swoole (container).

Suíte actual: **54+ testes** (auth, users, project, upload processing, storage, health, ACL).

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
| [docs/ROUTES.md](docs/ROUTES.md)   | Referência completa de rotas (auth, RBAC, **projetos**, **uploads**, taxonomias), bodies JSON e permissões |
| [docs/postman/README.md](docs/postman/README.md) | **Postman** — collection, environments, Runner e Newman |
| [docs/API.md](docs/API.md)         | Rotas `/api`, corpos, validações, códigos HTTP, exemplos `curl`, autenticação |
| [docs/PROJECT.md](docs/PROJECT.md) | Camadas DDD/hexagonal, regras de dependência, convenções, fluxo de pedidos    |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Git Flow, commits, hooks, checklist de PR                                     |

---

## Estrutura do repositório

```
app/
  Application/     # Casos de uso (handlers por feature: Project, Upload, Auth, …)
  Domain/          # Entidades, VOs, repositórios (interfaces), eventos
  Infrastructure/  # DB, Redis, mail, storage, fila, processamento GD, implementações
  Presentation/    # Controllers HTTP, FormRequest, Middleware, Exception handlers
  Job/             # Jobs async (views flush, processamento de upload)
config/            # Rotas, autoload, upload, async_queue, serviços
migrations/
test/              # Pest / PHPUnit (Unit/Project, Unit/Upload, Unit/Auth, …)
docs/              # ROUTES.md, API.md, arquitectura
hyper              # CLI Docker (estilo Sail)
.envrc             # direnv: PATH_add vendor/bin
```

Descrição detalhada: [docs/PROJECT.md](docs/PROJECT.md).

---

## Licença

Este projeto base segue a licença indicada em [LICENSE](LICENSE) e em [composer.json](composer.json) (Apache-2.0 no _skeleton_ oficial Hyperf).
