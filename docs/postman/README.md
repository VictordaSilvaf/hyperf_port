# Postman — VictorDev Hyperf API

Collection profissional para testes manuais, regressão e CI (Newman).

## Ficheiros

| Ficheiro | Descrição |
| -------- | --------- |
| [VictorDev-Hyperf-API.postman_collection.json](VictorDev-Hyperf-API.postman_collection.json) | Collection v2.1 (~75 requests) |
| [environments/victordev-local.postman_environment.json](environments/victordev-local.postman_environment.json) | Docker local (`http://127.0.0.1:9501`) |
| [environments/victordev-staging.postman_environment.json](environments/victordev-staging.postman_environment.json) | Staging (ajuste `baseUrl`) |
| [environments/victordev-production.postman_environment.json](environments/victordev-production.postman_environment.json) | Produção (sem passwords) |

## Importar no Postman

1. **Import** → arraste a collection + os 3 environments
2. Seleccione o environment **VictorDev — Local** (canto superior direito)
3. Confirme que a API está a correr: `./hyper up -d`
4. Execute **01 — Auth → Login — Admin** (grava `accessToken`)
5. Explore pastas **04–09 Admin** ou corra **99 — Flows (E2E)** no Collection Runner

## Estrutura da collection

```
00 — Setup & Health
01 — Auth (Success + Errors)
02 — Users (Public)
03 — Portfolio (Public) — pages, projects, taxonomies, site settings
04 — Admin — Users
05 — Admin — RBAC
06 — Admin — Uploads
07 — Admin — Projects (CRUD, Lifecycle, Images, Errors)
08 — Admin — Pages (CRUD, Lifecycle, Blocks)
09 — Admin — Site Settings
99 — Flows (E2E)
```

## Scripts incluídos

### Collection Pre-request

- Valida environment (`baseUrl`)
- Headers `Accept`, `Accept-Language`, `X-Request-Id`
- Bearer token automático (excepto rotas públicas)

### Collection Tests

- Tempo de resposta &lt; `maxResponseTimeMs`
- Content-Type JSON
- Guarda `lastStatusCode` / `lastResponseTime`

### Request Tests (exemplos)

- Login guarda `accessToken`
- Create project guarda `projectId` / `projectSlug`
- Upload guarda `uploadId`
- Casos de erro validam 401, 404, 422, 409

## Variáveis de environment

| Variável | Uso |
| -------- | --- |
| `baseUrl` | Host da API (sem `/api/v1`) |
| `apiPrefix` | `/api/v1` |
| `adminEmail` / `adminPassword` | Seed dev (`admin@victordev.com`) |
| `accessToken` | Preenchido após login |
| `projectId`, `projectSlug` | Fluxo de projectos |
| `pageId`, `pageSlug` | Fluxo de páginas (Page Builder) |
| `uploadId`, `projectImageId` | Galeria |
| `categoryId`, `technologyId`, `tagId` | Taxonomias (auto após GET lists) |

## Collection Runner (E2E)

1. Abra a collection → **Run**
2. Seleccione pasta **99 — Flows (E2E)** ou a collection inteira
3. Environment: **VictorDev — Local**
4. **Run VictorDev — Hyperf API**

Ordem recomendada manual: Health → Login Admin → Create Project → Publish → Public show.

## Newman (CI)

```bash
npm install -g newman

newman run docs/postman/VictorDev-Hyperf-API.postman_collection.json \
  -e docs/postman/environments/victordev-local.postman_environment.json \
  --folder "00 — Setup & Health" \
  --folder "01 — Auth" \
  --reporters cli,junit \
  --reporter-junit-export reports/postman-junit.xml
```

Requer API + PostgreSQL + Redis a correr (`./hyper up -d`).

## Regenerar a collection

Após alterar rotas em `config/routes.php`:

```bash
php scripts/generate-postman.php
```

## Upload multipart

Em **06 — Admin — Uploads**, seleccione um ficheiro local no campo `file` (form-data) antes de enviar.

## Referência API

Detalhes de bodies e permissões: [../ROUTES.md](../ROUTES.md)
