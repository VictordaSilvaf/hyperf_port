# Rotas da API

Referência completa de endpoints HTTP. Fonte: `config/routes.php`.

| Item | Valor |
|------|-------|
| **Base URL** | `/api/v1` |
| **Formato** | JSON (`Content-Type: application/json`) |
| **IDs** | UUID v4 (`xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx`) |
| **Autenticação admin** | `Authorization: Bearer <access_token>` + permissão RBAC |

---

## Índice

1. [Convenções](#convenções)
2. [Schemas reutilizáveis](#schemas-reutilizáveis)
3. [Geral & Health](#geral--health)
4. [Autenticação](#autenticação)
5. [Utilizadores (público)](#utilizadores-público)
6. [Páginas (público)](#páginas-público)
7. [Portfolio (público)](#portfolio-público)
8. [Admin — Utilizadores](#admin--utilizadores)
9. [Admin — RBAC](#admin--rbac)
10. [Admin — Uploads](#admin--uploads)
11. [Admin — Projetos](#admin--projetos)
12. [Admin — Páginas](#admin--páginas)
13. [Admin — Site Settings](#admin--site-settings)
14. [Permissões RBAC](#permissões-rbac)
15. [Códigos de erro comuns](#códigos-de-erro-comuns)

---

## Convenções

### Autenticação Bearer

Rotas **Auth 🔒** exigem token válido:

```http
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Autenticação + RBAC (Admin)

Rotas **Admin 🔒** exigem token **e** a permissão indicada na coluna *Permissão*.

### Paginação

Query params comuns:

| Param | Tipo | Default | Descrição |
|-------|------|---------|-----------|
| `page` | int | `1` | Página atual |
| `per_page` | int | `15` | Itens por página (máx. 100 onde aplicável) |

Resposta paginada:

```json
{
  "data": [],
  "meta": {
    "total": 42,
    "page": 1,
    "per_page": 15
  }
}
```

### Erro de validação (422)

```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Schemas reutilizáveis

### `ProjectDetail` — projeto completo (admin e público)

```json
{
  "id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "title": "Portfolio 3D",
  "slug": "portfolio-3d",
  "description": "Meu portfolio de modelagem 3D",
  "content": "# Sobre o projeto\n\nMarkdown livre...",
  "repository_url": "https://github.com/usuario/repo",
  "demo_url": "https://demo.exemplo.com",
  "thumbnail": "uploads/2026/07/abc123.png",
  "cover": "uploads/2026/07/cover.png",
  "status": "published",
  "featured": true,
  "published_at": "2026-07-04T15:00:00+00:00",
  "order": 1,
  "views": 1203,
  "categories": [
    { "id": "c1000003-0000-4000-8000-000000000001", "name": "3D", "slug": "3d" }
  ],
  "technologies": [
    { "id": "t1000002-0000-4000-8000-000000000001", "name": "React", "slug": "react" }
  ],
  "tags": [
    { "id": "g1000002-0000-4000-8000-000000000001", "name": "Portfolio", "slug": "portfolio" }
  ],
  "images": [
    {
      "id": "a1b2c3d4-e5f6-4789-a012-3456789abcde",
      "upload_id": "b2c3d4e5-f6a7-4890-b123-456789abcdef0",
      "caption": "Tela inicial",
      "order": 1,
      "url": "https://cdn.exemplo.com/dev/uploads/2026/07/screen.png",
      "path": "uploads/2026/07/screen.png"
    }
  ]
}
```

> Respostas admin envolvem em `{ "data": ProjectDetail }`.

### `ProjectSummary` — listagem

```json
{
  "id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "title": "Portfolio 3D",
  "slug": "portfolio-3d",
  "description": "Meu portfolio",
  "status": "published",
  "featured": true,
  "sort_order": 1,
  "order": 1,
  "thumbnail": "uploads/2026/07/abc123.png",
  "cover": "uploads/2026/07/cover.png",
  "published_at": "2026-07-04 15:00:00",
  "views": 1203,
  "created_at": "2026-07-01 10:00:00",
  "updated_at": "2026-07-04 15:00:00"
}
```

### `TaxonomyItem`

```json
{
  "id": "t1000001-0000-4000-8000-000000000001",
  "name": "Laravel",
  "slug": "laravel"
}
```

### `PageDetail` — página completa (admin e público)

Admin: blocos com `payload` bruto; SEO como objecto editável. Público: blocos enriquecidos (URLs, projectos, tecnologias) e SEO resolvido.

```json
{
  "id": "p1000001-0000-4000-8000-000000000001",
  "title": "Início",
  "slug": "inicio",
  "layout": "default",
  "is_home": true,
  "status": "published",
  "published_at": "2026-07-04T15:00:00+00:00",
  "order": 1,
  "seo": {
    "meta_title": "Início",
    "meta_description": "Portfolio e projetos de Victor Fernandes",
    "og_title": "Início",
    "og_description": "Portfolio e projetos",
    "og_image_id": "up000001-0000-4000-8000-000000000001",
    "canonical_url": null,
    "robots": "index,follow",
    "twitter_card": "summary_large_image"
  },
  "blocks": [ "...PageBlock..." ]
}
```

> Respostas admin envolvem em `{ "data": PageDetail }`. Na API pública, `seo` inclui `title`, `description`, `canonical`, `open_graph` e `twitter` já resolvidos.

### `PageBlock` — bloco de página

```json
{
  "id": "b1000001-0000-4000-8000-000000000001",
  "type": "hero",
  "order": 1,
  "payload": {
    "headline": "Olá, sou Victor",
    "subheadline": "Desenvolvedor full-stack",
    "image_id": "up000001-0000-4000-8000-000000000001",
    "cta": { "label": "Ver projetos", "href": "/projects" }
  },
  "settings": {}
}
```

Tipos suportados: `hero`, `markdown`, `image`, `gallery`, `featured_projects`, `project_list`, `tech_stack`, `cta`, `contact_form`, `embed`, `spacer`.

### `SiteSettings` — configurações globais do site

```json
{
  "nav": [],
  "footer": {},
  "social": {},
  "branding": {},
  "seo": {
    "site_name": "Victor Dev",
    "default_meta_description": "Portfolio e blog técnico",
    "default_og_image_id": "up000001-0000-4000-8000-000000000001",
    "twitter_site": "@victordev",
    "google_site_verification": null,
    "locale": "pt_BR"
  },
  "contact": {
    "email": "hello@victordev.com",
    "phone": "+351 900 000 000",
    "whatsapp": "https://wa.me/351900000000",
    "address": { "line1": "Rua Exemplo", "city": "Lisboa", "country": "PT" },
    "notification_email": "admin@victordev.com"
  },
  "updated_at": "2026-07-04T15:00:00+00:00"
}
```

> Resposta pública/admin envolve em `{ "data": SiteSettings }`.

### Senha forte (register, reset, change)

- Mínimo 8 caracteres
- Pelo menos 1 minúscula, 1 maiúscula e 1 dígito
- `password_confirmation` obrigatório onde há `confirmed`

---

## Geral & Health

### Index

| | |
|---|---|
| **GET** `/api/v1/` | |
| **POST** `/api/v1/` | |
| **HEAD** `/api/v1/` | |
| Auth | Não |

**Query**

| Param | Obrigatório | Descrição |
|-------|-------------|-----------|
| `user` | Não | Nome na mensagem (default: `Hyperf`) |

**Resposta 200**

```json
{
  "method": "GET",
  "message": "Hello Hyperf."
}
```

---

### Health — aggregate

| | |
|---|---|
| **GET** `/api/v1/health` | |
| Auth | Não |

**Resposta 200** (tudo OK) / **503** (algum probe falhou)

```json
{
  "status": "pass",
  "version": "1.0.0",
  "checks": {
    "application": { "status": "pass" },
    "database": { "status": "pass" },
    "redis": { "status": "pass" },
    "storage": { "status": "pass" }
  }
}
```

---

### Health — liveness

| | |
|---|---|
| **GET** `/api/v1/health/live` | |
| Auth | Não |

Processo vivo (sem checks externos pesados).

---

### Health — readiness

| | |
|---|---|
| **GET** `/api/v1/health/ready` | |
| Auth | Não |

Pronto para receber tráfego (DB, Redis, storage conforme config).

---

## Autenticação

Base: `/api/v1/auth`

### Registo

| | |
|---|---|
| **POST** `/api/v1/auth/register` | |
| Auth | Não |

**Body**

```json
{
  "name": "Victor Fernandes",
  "email": "victor@exemplo.com",
  "password": "Secret123",
  "password_confirmation": "Secret123"
}
```

**Resposta 200**

```json
{
  "id": "u0000001-0000-4000-8000-000000000001",
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "message": "Cadastro realizado com sucesso.",
  "roles": ["user"],
  "permissions": []
}
```

**Erros:** `409` e-mail já registado

---

### Login

| | |
|---|---|
| **POST** `/api/v1/auth/login` | |
| Auth | Não |

**Body**

```json
{
  "email": "victor@exemplo.com",
  "password": "Secret123"
}
```

**Resposta 200**

```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "roles": ["admin"],
  "permissions": ["projects.view", "projects.create"]
}
```

**Erros:** `401` credenciais inválidas

---

### Logout

| | |
|---|---|
| **POST** `/api/v1/auth/logout` | |
| Auth | Não (stateless) |

**Resposta 200**

```json
{
  "message": "O token é descartado no cliente. Este endpoint não altera estado no servidor (API stateless)."
}
```

---

### Esqueci a senha

| | |
|---|---|
| **POST** `/api/v1/auth/forgot-password` | |
| Auth | Não |

**Body**

```json
{
  "email": "victor@exemplo.com"
}
```

**Resposta 200** (sempre genérica)

```json
{
  "message": "Se existir uma conta para esse e-mail, enviamos as instruções de redefinição de senha."
}
```

---

### Redefinir senha

| | |
|---|---|
| **POST** `/api/v1/auth/reset-password` | |
| Auth | Não |

**Body**

```json
{
  "code": "123456",
  "password": "NovaSecret123",
  "password_confirmation": "NovaSecret123"
}
```

**Resposta 200**

```json
{
  "message": "Senha redefinida. Você já pode entrar com a nova senha."
}
```

**Erros:** `422` código inválido ou expirado

---

### Refresh token

| | |
|---|---|
| **POST** `/api/v1/auth/refresh` | |
| Auth | 🔒 Token ainda válido |

**Body** — vazio ou `{}`

**Resposta 200**

```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "roles": ["admin"],
  "permissions": ["projects.view"]
}
```

---

### Alterar senha

| | |
|---|---|
| **POST** `/api/v1/auth/change-password` | |
| Auth | 🔒 |

**Body**

```json
{
  "current_password": "Secret123",
  "password": "NovaSecret123",
  "password_confirmation": "NovaSecret123"
}
```

**Resposta 200**

```json
{
  "message": "Senha atualizada."
}
```

---

## Utilizadores (público)

Base: `/api/v1/users`

### Perfil autenticado

| | |
|---|---|
| **GET** `/api/v1/users/me` | |
| Auth | 🔒 |

**Resposta 200**

```json
{
  "id": "u0000001-0000-4000-8000-000000000001",
  "name": "Victor Fernandes",
  "email": "victor@exemplo.com",
  "roles": ["admin"],
  "permissions": ["projects.view", "projects.create"]
}
```

---

### Perfil por ID

| | |
|---|---|
| **GET** `/api/v1/users/{id}` | |
| Auth | Não |

**Resposta 200**

```json
{
  "id": "u0000001-0000-4000-8000-000000000001",
  "name": "Victor Fernandes",
  "email": "victor@exemplo.com"
}
```

**Erros:** `404` utilizador não encontrado

---

## Páginas (público)

Apenas páginas com `status: published` aparecem nestas rotas. A home é a página com `is_home: true`.

### Home

| | |
|---|---|
| **GET** `/api/v1/pages/home` | |
| Auth | Não |

Retorna a página marcada como home, com blocos enriquecidos e SEO resolvido (cache Redis, TTL 300s).

**Resposta 200**

```json
{
  "data": { "...PageDetail (público)..." }
}
```

**Erros:** `404` nenhuma home publicada

---

### Listar páginas

| | |
|---|---|
| **GET** `/api/v1/pages` | |
| Auth | Não |

Lista resumida para navegação (menu, sitemap).

**Query**

| Param | Tipo | Descrição |
|-------|------|-----------|
| `page` | int | Paginação |
| `per_page` | int | Itens por página (máx. 100) |

**Resposta 200**

```json
{
  "data": [
    {
      "id": "p1000001-0000-4000-8000-000000000001",
      "title": "Início",
      "slug": "inicio",
      "status": "published",
      "is_home": true,
      "sort_order": 1,
      "published_at": "2026-07-04T15:00:00+00:00"
    }
  ],
  "meta": { "total": 3, "page": 1, "per_page": 15 }
}
```

---

### Detalhe por slug

| | |
|---|---|
| **GET** `/api/v1/pages/{slug}` | |
| Auth | Não |

**Resposta 200**

```json
{
  "data": { "...PageDetail (público)..." }
}
```

**Erros:** `404` página não encontrada ou não publicada

---

### Tipos de bloco

| | |
|---|---|
| **GET** `/api/v1/block-types` | |
| Auth | Não |

Catálogo para o editor (Page Builder): tipo, label e JSON Schema do `payload`.

**Resposta 200**

```json
{
  "data": [
    {
      "type": "hero",
      "label": "Hero",
      "schema": {
        "type": "object",
        "required": ["headline"],
        "properties": {
          "headline": { "type": "string", "maxLength": 200 }
        }
      }
    }
  ]
}
```

---

### Configurações do site

| | |
|---|---|
| **GET** `/api/v1/site/settings` | |
| Auth | Não |

Nav, footer, social, branding e defaults SEO. Cache Redis (TTL 300s).

**Resposta 200**

```json
{
  "data": { "...SiteSettings..." }
}
```

---

## Portfolio (público)

Apenas projetos com `status: published` aparecem nestas rotas (exceto `/search`, que também filtra publicados).

### Listar projetos

| | |
|---|---|
| **GET** `/api/v1/projects` | |
| Auth | Não |

**Query**

| Param | Tipo | Descrição |
|-------|------|-----------|
| `page` | int | Paginação |
| `per_page` | int | Itens por página |
| `search` | string | Título, slug, descrição, conteúdo |
| `technology` | string | Slug da tecnologia (ex: `react`) |
| `category` | string | Slug da categoria (ex: `3d`) |
| `tag` | string | Slug da tag |
| `featured` | bool | `true` / `false` |
| `sort` | string | `sort_order`, `title`, `created_at`, `published_at`, `views` |
| `direction` | string | `asc` ou `desc` |

**Exemplos**

```http
GET /api/v1/projects?page=1&featured=true
GET /api/v1/projects?technology=laravel
GET /api/v1/projects?search=react&sort=published_at&direction=desc
```

**Resposta 200**

```json
{
  "data": [ "ProjectSummary..." ],
  "meta": { "total": 12, "page": 1, "per_page": 15 }
}
```

---

### Detalhe por slug

| | |
|---|---|
| **GET** `/api/v1/projects/{slug}` | |
| Auth | Não |

Incrementa contador de views (Redis → flush assíncrono para PostgreSQL).

**Resposta 200**

```json
{
  "data": { "...ProjectDetail..." }
}
```

**Erros:** `404` projeto não encontrado ou não publicado

---

### Projetos relacionados

| | |
|---|---|
| **GET** `/api/v1/projects/{slug}/related` | |
| Auth | Não |

Relacionamento por categorias, tecnologias e tags partilhadas.

**Resposta 200**

```json
{
  "data": [ "ProjectSummary..." ]
}
```

---

### Busca global

| | |
|---|---|
| **GET** `/api/v1/search` | |
| Auth | Não |

**Query**

| Param | Obrigatório | Descrição |
|-------|-------------|-----------|
| `q` | Sim | Termo de busca |
| `page` | Não | Paginação |
| `per_page` | Não | Itens por página |

**Resposta 200**

```json
{
  "projects": [ "ProjectSummary..." ],
  "meta": { "total": 3, "page": 1, "per_page": 15 }
}
```

---

### Taxonomias

| Método | Rota | Resposta |
|--------|------|----------|
| GET | `/api/v1/categories` | `{ "data": [ TaxonomyItem ] }` |
| GET | `/api/v1/technologies` | `{ "data": [ TaxonomyItem ] }` |
| GET | `/api/v1/tags` | `{ "data": [ TaxonomyItem ] }` |

---

## Admin — Utilizadores

Base: `/api/v1/admin/users` — todas exigem **Admin 🔒**

### Listar

| | |
|---|---|
| **GET** `/api/v1/admin/users` | |
| Permissão | `users.view` |

**Query:** `page`, `per_page`, `search` (nome ou e-mail)

**Resposta 200**

```json
{
  "total": 25,
  "items": [
    {
      "id": "u0000001-0000-4000-8000-000000000001",
      "name": "Victor Fernandes",
      "email": "victor@exemplo.com",
      "created_at": "2026-05-03 12:00:00",
      "updated_at": "2026-05-03 12:00:00"
    }
  ]
}
```

---

### Criar

| | |
|---|---|
| **POST** `/api/v1/admin/users` | |
| Permissão | `users.create` |

**Body**

```json
{
  "name": "Novo Utilizador",
  "email": "novo@exemplo.com",
  "password": "Secret123",
  "password_confirmation": "Secret123"
}
```

**Resposta 200**

```json
{
  "id": "u0000002-0000-4000-8000-000000000002",
  "message": "Utilizador criado."
}
```

---

### Detalhe

| | |
|---|---|
| **GET** `/api/v1/admin/users/{id}` | |
| Permissão | `users.view` |

**Resposta 200**

```json
{
  "id": "u0000001-0000-4000-8000-000000000001",
  "name": "Victor Fernandes",
  "email": "victor@exemplo.com",
  "roles": ["admin"],
  "permissions": ["projects.view", "users.view"]
}
```

---

### Atualizar

| | |
|---|---|
| **PUT** `/api/v1/admin/users/{id}` | |
| Permissão | `users.update` |

**Body**

```json
{
  "name": "Victor Silva",
  "email": "victor.silva@exemplo.com"
}
```

**Resposta 200**

```json
{
  "message": "Utilizador atualizado."
}
```

---

## Admin — RBAC

Base: `/api/v1/admin` — todas exigem **Admin 🔒**

### Papéis

| Método | Rota | Permissão | Descrição |
|--------|------|-----------|-----------|
| GET | `/roles` | `roles.view` | Listar papéis |
| POST | `/roles` | `roles.create` | Criar papel |
| DELETE | `/roles/{id}` | `roles.delete` | Eliminar papel não-sistema |
| PUT | `/roles/{id}/permissions` | `roles.assign_permissions` | Sync permissões do papel |

**POST `/roles` — Body**

```json
{
  "name": "Editor",
  "slug": "editor"
}
```

**Resposta 200**

```json
{
  "id": "r0000004-0000-4000-8000-000000000004",
  "message": "Role criada."
}
```

**PUT `/roles/{id}/permissions` — Body**

```json
{
  "permission_ids": [
    "b0000020-0000-4000-8000-000000000001",
    "b0000021-0000-4000-8000-000000000001"
  ]
}
```

**Resposta 200**

```json
{
  "message": "Permissões da role atualizadas."
}
```

---

### Permissões

| | |
|---|---|
| **GET** `/api/v1/admin/permissions` | |
| Permissão | `permissions.view` |

**Resposta 200**

```json
{
  "data": [
    {
      "id": "b0000020-0000-4000-8000-000000000001",
      "slug": "projects.view",
      "description": "Listar projetos"
    }
  ]
}
```

---

### Roles do utilizador

| | |
|---|---|
| **PUT** `/api/v1/admin/users/{id}/roles` | |
| Permissão | `users.assign_roles` |

**Body**

```json
{
  "role_ids": [
    "a0000002-0000-4000-8000-000000000001"
  ]
}
```

**Resposta 200**

```json
{
  "message": "Roles do usuário atualizadas."
}
```

---

## Admin — Uploads

| | |
|---|---|
| **POST** `/api/v1/admin/uploads` | |
| Permissão | `uploads.create` |
| Content-Type | `multipart/form-data` |

**Form fields**

| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| `file` | file | Sim |

**Resposta 200**

```json
{
  "id": "up000001-0000-4000-8000-000000000001",
  "url": "https://cdn.exemplo.com/dev/uploads/2026/07/abc.png",
  "path": "uploads/2026/07/abc.png"
}
```

> Storage: MinIO (dev) / Cloudflare R2 (prod) via API S3-compatible.

---

## Admin — Projetos

Base: `/api/v1/admin/projects` — todas exigem **Admin 🔒**

### Estatísticas

| | |
|---|---|
| **GET** `/api/v1/admin/projects/statistics` | |
| Permissão | `projects.view` |

**Resposta 200**

```json
{
  "published": 12,
  "draft": 4,
  "archived": 2,
  "views": 12003,
  "featured": 5
}
```

---

### Listar (admin)

| | |
|---|---|
| **GET** `/api/v1/admin/projects` | |
| Permissão | `projects.view` |

**Query:** mesmos filtros do público + `status` (`draft`, `published`, `archived`), `with_trashed` (bool)

**Resposta 200**

```json
{
  "data": [ "ProjectSummary..." ],
  "meta": { "total": 18, "page": 1, "per_page": 15 }
}
```

---

### Criar

| | |
|---|---|
| **POST** `/api/v1/admin/projects` | |
| Permissão | `projects.create` |

**Body**

```json
{
  "title": "Portfolio 3D",
  "slug": "portfolio-3d",
  "description": "Meu portfolio",
  "content": "# Markdown\n\nConteúdo rico...",
  "repository_url": "https://github.com/usuario/repo",
  "demo_url": "https://demo.exemplo.com",
  "thumbnail": "uploads/2026/07/thumb.png",
  "cover": "uploads/2026/07/cover.png",
  "status": "draft",
  "featured": true,
  "categories": [
    "c1000003-0000-4000-8000-000000000001"
  ],
  "technologies": [
    "t1000002-0000-4000-8000-000000000001",
    "t1000003-0000-4000-8000-000000000001"
  ],
  "tags": [
    "g1000002-0000-4000-8000-000000000001"
  ]
}
```

| Campo | Obrigatório | Notas |
|-------|-------------|-------|
| `title` | Sim | 2–200 chars |
| `slug` | Não | Auto-gerado do título se omitido |
| `status` | Não | Default `draft` |
| `categories`, `technologies`, `tags` | Não | Array de UUIDs |

**Resposta 200**

```json
{
  "data": { "...ProjectDetail..." }
}
```

**Erros:** `409` slug já em uso

---

### Detalhe

| | |
|---|---|
| **GET** `/api/v1/admin/projects/{id}` | |
| Permissão | `projects.view` |

**Resposta 200**

```json
{
  "data": { "...ProjectDetail..." }
}
```

---

### Atualizar (completo)

| | |
|---|---|
| **PUT** `/api/v1/admin/projects/{id}` | |
| Permissão | `projects.update` |

**Body** — mesmo schema do POST (todos os campos obrigatórios do create).

**Resposta 200**

```json
{
  "data": { "...ProjectDetail..." }
}
```

---

### Atualizar (parcial)

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}` | |
| Permissão | `projects.update` |

**Body** — envie apenas os campos a alterar:

```json
{
  "featured": true
}
```

```json
{
  "title": "Novo título",
  "order": 3,
  "published_at": "2026-07-04T15:00:00+00:00"
}
```

**Resposta 200**

```json
{
  "data": { "...ProjectDetail..." }
}
```

---

### Publicar

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/publish` | |
| Permissão | `projects.publish` |

**Body** (opcional)

```json
{
  "published_at": "2026-07-04T15:00:00+00:00"
}
```

**Resposta 200** — `{ "data": ProjectDetail }` com `status: "published"`. Invalida cache público.

---

### Arquivar

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/archive` | |
| Permissão | `projects.publish` |

**Body** — vazio

**Resposta 200** — `{ "data": ProjectDetail }` com `status: "archived"`

---

### Voltar para draft

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/draft` | |
| Permissão | `projects.publish` |

**Resposta 200** — `{ "data": ProjectDetail }` com `status: "draft"`, `published_at: null`

---

### Soft delete

| | |
|---|---|
| **DELETE** `/api/v1/admin/projects/{id}` | |
| Permissão | `projects.delete` |

**Resposta 200**

```json
{
  "message": "Projeto eliminado."
}
```

---

### Restaurar

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/restore` | |
| Permissão | `projects.update` |

**Resposta 200** — `{ "data": ProjectDetail }`

---

### Exclusão definitiva

| | |
|---|---|
| **DELETE** `/api/v1/admin/projects/{id}/force` | |
| Permissão | `projects.delete` |

Remove permanentemente (inclui relações em cascade).

---

### Reordenar

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/order` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "projects": [
    { "id": "f47ac10b-58cc-4372-a567-0e02b2c3d479", "order": 1 },
    { "id": "a1b2c3d4-e5f6-4789-a012-3456789abcde", "order": 2 }
  ]
}
```

**Resposta 200**

```json
{
  "message": "Ordem dos projetos atualizada."
}
```

---

### Duplicar

| | |
|---|---|
| **POST** `/api/v1/admin/projects/{id}/duplicate` | |
| Permissão | `projects.create` |

Cria cópia em `draft` com slug `{original}-copy`, relações copiadas.

**Resposta 200**

```json
{
  "data": { "...ProjectDetail..." }
}
```

---

### Imagens

#### Adicionar imagem

| | |
|---|---|
| **POST** `/api/v1/admin/projects/{id}/images` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "image_id": "up000001-0000-4000-8000-000000000001",
  "caption": "Tela inicial"
}
```

> `image_id` = UUID do upload (`POST /admin/uploads`).

**Resposta 200**

```json
{
  "id": "img00001-0000-4000-8000-000000000001"
}
```

#### Remover imagem

| | |
|---|---|
| **DELETE** `/api/v1/admin/projects/{id}/images/{imageId}` | |
| Permissão | `projects.update` |

#### Reordenar imagens

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/images/order` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "images": [
    { "id": "img00001-0000-4000-8000-000000000001", "order": 1 },
    { "id": "img00002-0000-4000-8000-000000000002", "order": 2 }
  ]
}
```

#### Definir thumbnail

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/thumbnail` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "image_id": "up000001-0000-4000-8000-000000000001"
}
```

#### Definir cover

| | |
|---|---|
| **PATCH** `/api/v1/admin/projects/{id}/cover` | |
| Permissão | `projects.update` |

**Body** — igual ao thumbnail.

---

### Sync taxonomias

#### Categorias

| | |
|---|---|
| **PUT** `/api/v1/admin/projects/{id}/categories` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "categories": [
    "c1000001-0000-4000-8000-000000000001",
    "c1000003-0000-4000-8000-000000000001"
  ]
}
```

#### Tecnologias

| | |
|---|---|
| **PUT** `/api/v1/admin/projects/{id}/technologies` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "technologies": [
    "t1000001-0000-4000-8000-000000000001",
    "t1000002-0000-4000-8000-000000000001"
  ]
}
```

#### Tags

| | |
|---|---|
| **PUT** `/api/v1/admin/projects/{id}/tags` | |
| Permissão | `projects.update` |

**Body**

```json
{
  "tags": [
    "g1000001-0000-4000-8000-000000000001",
    "g1000002-0000-4000-8000-000000000001"
  ]
}
```

**Resposta 200** (sync) — `{ "data": ProjectDetail }`

---

## Admin — Páginas

Base: `/api/v1/admin/pages` — todas exigem **Admin 🔒**

Page Builder: páginas compostas por blocos validados (`PUT /{id}/blocks` substitui a lista completa).

### Reordenar

| | |
|---|---|
| **PATCH** `/api/v1/admin/pages/order` | |
| Permissão | `pages.update` |

**Body**

```json
{
  "items": [
    { "id": "p1000001-0000-4000-8000-000000000001", "sort_order": 1 },
    { "id": "p1000002-0000-4000-8000-000000000002", "sort_order": 2 }
  ]
}
```

**Resposta 200**

```json
{
  "message": "Ordem das páginas actualizada."
}
```

---

### Listar

| | |
|---|---|
| **GET** `/api/v1/admin/pages` | |
| Permissão | `pages.view` |

**Query:** `page`, `per_page`

**Resposta 200** — paginada (mesmo formato de item que a listagem pública, inclui `draft`/`archived`).

---

### Criar

| | |
|---|---|
| **POST** `/api/v1/admin/pages` | |
| Permissão | `pages.create` |

**Body**

```json
{
  "title": "Sobre",
  "slug": "sobre",
  "layout": "default",
  "is_home": false,
  "status": "draft",
  "seo": {
    "meta_title": "Sobre mim",
    "meta_description": "Biografia e experiência"
  }
}
```

| Campo | Obrigatório | Notas |
|-------|-------------|-------|
| `title` | Sim | 2–200 chars |
| `slug` | Não | Auto-gerado do título se omitido |
| `layout` | Não | `default`, `full-width`, `landing` |
| `is_home` | Não | Default `false`; apenas uma página pode ser home |
| `status` | Não | Default `draft` |

**Resposta 200**

```json
{
  "data": { "...PageDetail..." }
}
```

**Erros:** `409` slug já em uso

---

### Detalhe

| | |
|---|---|
| **GET** `/api/v1/admin/pages/{id}` | |
| Permissão | `pages.view` |

**Query:** `with_trashed=true` (opcional) — inclui soft-deleted

**Resposta 200**

```json
{
  "data": { "...PageDetail..." }
}
```

---

### Atualizar (completo)

| | |
|---|---|
| **PUT** `/api/v1/admin/pages/{id}` | |
| Permissão | `pages.update` |

**Body** — mesmo schema do POST (todos os campos obrigatórios do create).

**Resposta 200** — `{ "data": PageDetail }`

---

### Atualizar (parcial)

| | |
|---|---|
| **PATCH** `/api/v1/admin/pages/{id}` | |
| Permissão | `pages.update` |

**Body** — envie apenas os campos a alterar:

```json
{
  "title": "Novo título",
  "is_home": true,
  "order": 2
}
```

**Resposta 200** — `{ "data": PageDetail }`

---

### Publicar

| | |
|---|---|
| **PATCH** `/api/v1/admin/pages/{id}/publish` | |
| Permissão | `pages.publish` |

**Body** (opcional)

```json
{
  "published_at": "2026-07-04T15:00:00+00:00"
}
```

**Resposta 200** — `{ "data": PageDetail }` com `status: "published"`. Invalida cache público.

---

### Arquivar

| | |
|---|---|
| **PATCH** `/api/v1/admin/pages/{id}/archive` | |
| Permissão | `pages.publish` |

**Resposta 200** — `{ "data": PageDetail }` com `status: "archived"`

---

### Voltar para draft

| | |
|---|---|
| **PATCH** `/api/v1/admin/pages/{id}/draft` | |
| Permissão | `pages.publish` |

**Resposta 200** — `{ "data": PageDetail }` com `status: "draft"`, `published_at: null`

---

### Soft delete

| | |
|---|---|
| **DELETE** `/api/v1/admin/pages/{id}` | |
| Permissão | `pages.delete` |

**Resposta 200**

```json
{
  "message": "Página eliminada."
}
```

---

### Restaurar

| | |
|---|---|
| **PATCH** `/api/v1/admin/pages/{id}/restore` | |
| Permissão | `pages.update` |

**Resposta 200** — `{ "data": PageDetail }`

---

### Exclusão definitiva

| | |
|---|---|
| **DELETE** `/api/v1/admin/pages/{id}/force` | |
| Permissão | `pages.delete` |

Remove permanentemente (inclui blocos em cascade).

---

### Duplicar

| | |
|---|---|
| **POST** `/api/v1/admin/pages/{id}/duplicate` | |
| Permissão | `pages.create` |

**Resposta 200** — `{ "data": PageDetail }` (cópia em `draft`, slug único)

---

### Sync blocos

| | |
|---|---|
| **PUT** `/api/v1/admin/pages/{id}/blocks` | |
| Permissão | `pages.update` |

Substitui **todos** os blocos da página. Valida `type` e `payload` conforme o registo de blocos.

**Body**

```json
{
  "blocks": [
    {
      "type": "hero",
      "payload": {
        "headline": "Olá, sou Victor",
        "subheadline": "Desenvolvedor full-stack",
        "image_id": "up000001-0000-4000-8000-000000000001"
      },
      "settings": {}
    },
    {
      "type": "markdown",
      "payload": {
        "content": "# Sobre\n\nTexto em Markdown..."
      },
      "settings": {}
    },
    {
      "type": "featured_projects",
      "payload": {
        "project_ids": [
          "f47ac10b-58cc-4372-a567-0e02b2c3d479"
        ]
      },
      "settings": {}
    }
  ]
}
```

**Resposta 200** — `{ "data": PageDetail }`. Invalida cache público.

**Erros:** `422` payload de bloco inválido

---

## Admin — Site Settings

Singleton de configuração global do site.

### Obter (admin)

| | |
|---|---|
| **GET** `/api/v1/admin/site/settings` | |
| Permissão | `site.update` |

**Resposta 200**

```json
{
  "data": { "...SiteSettings..." }
}
```

---

### Actualizar

| | |
|---|---|
| **PUT** `/api/v1/admin/site/settings` | |
| Permissão | `site.update` |

**Body** — parcial (`sometimes`); envie apenas secções a alterar:

```json
{
  "nav": [
    { "label": "Início", "href": "/" },
    { "label": "Projetos", "href": "/projects" }
  ],
  "seo": {
    "site_name": "Victor Dev",
    "default_meta_description": "Portfolio e blog técnico",
    "locale": "pt_BR"
  },
  "branding": {
    "logo_upload_id": "up000001-0000-4000-8000-000000000001"
  }
}
```

**Resposta 200**

```json
{
  "data": { "...SiteSettings..." }
}
```

Invalida cache público de `/site/settings` e SEO de páginas.

---

## Permissões RBAC

| Slug | Descrição |
|------|-----------|
| `users.view` | Ver utilizadores |
| `users.create` | Criar utilizadores |
| `users.update` | Editar utilizadores |
| `users.assign_roles` | Atribuir papéis |
| `roles.view` | Listar papéis |
| `roles.create` | Criar papéis |
| `roles.delete` | Eliminar papéis |
| `roles.assign_permissions` | Definir permissões de um papel |
| `permissions.view` | Listar permissões |
| `projects.view` | Listar projetos |
| `projects.create` | Criar projetos |
| `projects.update` | Editar / reordenar projetos |
| `projects.delete` | Eliminar projetos |
| `projects.publish` | Publicar / arquivar / draft (projetos) |
| `pages.view` | Listar páginas |
| `pages.create` | Criar / duplicar páginas |
| `pages.update` | Editar / reordenar páginas / sync blocos |
| `pages.delete` | Eliminar páginas |
| `pages.publish` | Publicar / arquivar / draft (páginas) |
| `site.update` | Ver e editar configurações do site |
| `uploads.create` | Upload de ficheiros |

Papéis seed: `admin` (todas), `manager` (subset), `user` (básico).

---

## Códigos de erro comuns

| HTTP | Situação | Exemplo `message` |
|------|----------|-------------------|
| 401 | Token ausente, inválido ou expirado | `Não autorizado` |
| 403 | Sem permissão RBAC | `Proibido` |
| 404 | Recurso não encontrado | `Projeto não encontrado.` / `Página não encontrada.` |
| 409 | Conflito (slug, e-mail, role) | `Slug de projeto já em uso.` / `Slug de página já em uso.` |
| 422 | Validação falhou | `Validation failed` + `errors` |
| 503 | Health check falhou | `status: fail` nos probes |

---

## Resumo rápido — todas as rotas

| Método | Rota | Auth |
|--------|------|------|
| GET/POST/HEAD | `/api/v1/` | — |
| GET | `/api/v1/health` | — |
| GET | `/api/v1/health/live` | — |
| GET | `/api/v1/health/ready` | — |
| POST | `/api/v1/auth/register` | — |
| POST | `/api/v1/auth/login` | — |
| POST | `/api/v1/auth/logout` | — |
| POST | `/api/v1/auth/forgot-password` | — |
| POST | `/api/v1/auth/reset-password` | — |
| POST | `/api/v1/auth/refresh` | 🔒 |
| POST | `/api/v1/auth/change-password` | 🔒 |
| GET | `/api/v1/users/me` | 🔒 |
| GET | `/api/v1/users/{id}` | — |
| GET | `/api/v1/pages/home` | — |
| GET | `/api/v1/pages` | — |
| GET | `/api/v1/pages/{slug}` | — |
| GET | `/api/v1/block-types` | — |
| GET | `/api/v1/site/settings` | — |
| POST | `/api/v1/contact` | — |
| GET | `/api/v1/projects` | — |
| GET | `/api/v1/projects/{slug}` | — |
| GET | `/api/v1/projects/{slug}/related` | — |
| GET | `/api/v1/categories` | — |
| GET | `/api/v1/technologies` | — |
| GET | `/api/v1/tags` | — |
| GET | `/api/v1/search` | — |
| GET | `/api/v1/admin/users` | Admin |
| POST | `/api/v1/admin/users` | Admin |
| GET | `/api/v1/admin/users/{id}` | Admin |
| PUT | `/api/v1/admin/users/{id}` | Admin |
| GET | `/api/v1/admin/roles` | Admin |
| POST | `/api/v1/admin/roles` | Admin |
| DELETE | `/api/v1/admin/roles/{id}` | Admin |
| PUT | `/api/v1/admin/roles/{id}/permissions` | Admin |
| GET | `/api/v1/admin/permissions` | Admin |
| PUT | `/api/v1/admin/users/{id}/roles` | Admin |
| POST | `/api/v1/admin/uploads` | Admin |
| GET | `/api/v1/admin/projects/statistics` | Admin |
| PATCH | `/api/v1/admin/projects/order` | Admin |
| GET | `/api/v1/admin/projects` | Admin |
| POST | `/api/v1/admin/projects` | Admin |
| GET | `/api/v1/admin/projects/{id}` | Admin |
| PUT | `/api/v1/admin/projects/{id}` | Admin |
| PATCH | `/api/v1/admin/projects/{id}` | Admin |
| DELETE | `/api/v1/admin/projects/{id}` | Admin |
| DELETE | `/api/v1/admin/projects/{id}/force` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/restore` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/publish` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/archive` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/draft` | Admin |
| POST | `/api/v1/admin/projects/{id}/duplicate` | Admin |
| POST | `/api/v1/admin/projects/{id}/images` | Admin |
| DELETE | `/api/v1/admin/projects/{id}/images/{imageId}` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/images/order` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/thumbnail` | Admin |
| PATCH | `/api/v1/admin/projects/{id}/cover` | Admin |
| PUT | `/api/v1/admin/projects/{id}/categories` | Admin |
| PUT | `/api/v1/admin/projects/{id}/technologies` | Admin |
| PUT | `/api/v1/admin/projects/{id}/tags` | Admin |
| PATCH | `/api/v1/admin/pages/order` | Admin |
| GET | `/api/v1/admin/pages` | Admin |
| POST | `/api/v1/admin/pages` | Admin |
| GET | `/api/v1/admin/pages/{id}` | Admin |
| PUT | `/api/v1/admin/pages/{id}` | Admin |
| PATCH | `/api/v1/admin/pages/{id}` | Admin |
| DELETE | `/api/v1/admin/pages/{id}` | Admin |
| DELETE | `/api/v1/admin/pages/{id}/force` | Admin |
| PATCH | `/api/v1/admin/pages/{id}/restore` | Admin |
| PATCH | `/api/v1/admin/pages/{id}/publish` | Admin |
| PATCH | `/api/v1/admin/pages/{id}/archive` | Admin |
| PATCH | `/api/v1/admin/pages/{id}/draft` | Admin |
| POST | `/api/v1/admin/pages/{id}/duplicate` | Admin |
| PUT | `/api/v1/admin/pages/{id}/blocks` | Admin |
| GET | `/api/v1/admin/site/settings` | Admin |
| PUT | `/api/v1/admin/site/settings` | Admin |
| GET | `/api/v1/admin/contact/messages` | Admin |
| GET | `/api/v1/admin/contact/messages/{id}` | Admin |
| PATCH | `/api/v1/admin/contact/messages/{id}` | Admin |

---

*Última actualização: alinhado com `config/routes.php` e handlers em `app/Application/`.*
