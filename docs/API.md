# Referência da API HTTP

Todas as rotas documentadas abaixo estão registadas em `config/routes.php` dentro do grupo com prefixo **`/api/v1`**.

**Base path:** `/api/v1`  
**Formato:** JSON (`Content-Type: application/json` nos pedidos com corpo).

---

## Convenções gerais

### Erros de validação

Quando o corpo não cumpre as regras do `FormRequest`, a API responde com JSON no formato:

```json
{
  "message": "Validation failed",
  "errors": { "campo": ["mensagem"] }
}
```

O código HTTP segue o definido pela excepção de validação do Hyperf (tipicamente **422**).

### Erros 500 e modo debug

A configuração `debug` em `config/config.php` reflecte `APP_DEBUG`. Quando `APP_DEBUG=true`, respostas **500** podem incluir `message` detalhada, `exception`, `file`, `line` e `trace`. Em produção, mantém-se uma mensagem genérica (`Internal Server Error.`). Ver `app/Presentation/Http/Exception/Handler/AppExceptionHandler.php`.

### Autenticação Bearer

Rotas marcadas como **autenticadas** exigem o cabeçalho:

```http
Authorization: Bearer <access_token>
```

O token é emitido no registo e no login. Implementação: `app/Infrastructure/Auth/SignedAccessTokenIssuer.php`.

Variáveis de ambiente relevantes (sem expor valores em documentação):

- `APP_LOCALE` — idioma das mensagens da API e da validação (`pt_BR` ou `en`; ficheiros em `storage/languages/`).
- `APP_FALLBACK_LOCALE` — idioma de recurso quando falta tradução.
- `APP_AUTH_SECRET` — segredo HMAC para assinar o token.
- `APP_AUTH_TOKEN_TTL` — TTL do token em segundos (por omissão `604800`).

O payload inclui `exp` (Unix). Tokens **expirados** são rejeitados pelo middleware; não há refresh token separado.

### Refresh do access token

`POST /api/v1/auth/refresh` só funciona enquanto o token enviado no `Authorization` **ainda é válido** (assinatura correcta e `exp` no futuro). Para prolongar sessões no cliente, chame refresh **antes** da expiração. Se o token já expirou, é necessário voltar a fazer login.

---

## Rotas

### `GET` / `POST` / `HEAD` — `/api/v1/`

Health / exemplo de index.

| Query  | Obrigatório | Descrição                                         |
| ------ | ----------- | ------------------------------------------------- |
| `user` | Não         | Nome a incluir na mensagem (por omissão `Hyperf`) |

**Resposta 200**

```json
{
  "method": "GET",
  "message": "Hello Hyperf."
}
```

Controlador: `app/Controller/IndexController.php`.

---

## Health checks (observabilidade)

Rotas **públicas** (sem autenticação), pensadas para Kubernetes, Docker Compose e monitorização.

| Rota | Uso |
|------|-----|
| `GET /api/v1/health/live` | **Liveness** — processo HTTP activo (não verifica DB/Redis) |
| `GET /api/v1/health/ready` | **Readiness** — pronto para receber tráfego |
| `GET /api/v1/health` | Alias de readiness (monitorização / load balancer) |

### Dependências verificadas em `/ready` e `/health`

| Componente | Quando é verificado |
|------------|---------------------|
| `app` | Sempre |
| `database` | `APP_USER_REPOSITORY=db` |
| `redis` | `APP_AUTH_RESET_STORE=redis` |
| `storage` | `FILESYSTEM_DRIVER=minio` ou `r2` (opcional na readiness; ver `APP_STORAGE_HEALTH_REQUIRED`) |

Com `APP_USER_REPOSITORY=memory` e `APP_AUTH_RESET_STORE=array` (padrão dev), readiness valida apenas `app`.

### `GET` — `/api/v1/health/live`

**Resposta 200** (sempre que o servidor responder)

```json
{
  "status": "pass",
  "service": "VictorDev",
  "environment": "dev",
  "timestamp": "2026-07-02T21:00:00+00:00",
  "checks": {
    "app": { "status": "pass" }
  }
}
```

### `GET` — `/api/v1/health/ready` e `/api/v1/health`

**Resposta 200** — todas as dependências configuradas OK.

**Resposta 503** — alguma dependência falhou.

```json
{
  "status": "fail",
  "service": "VictorDev",
  "environment": "dev",
  "timestamp": "2026-07-02T21:00:00+00:00",
  "checks": {
    "app": { "status": "pass" },
    "database": {
      "status": "fail",
      "message": "Database connection failed.",
      "latency_ms": 12.34
    }
  }
}
```

Controlador: `app/Controller/HealthController.php`. Caso de uso: `app/Application/Health/GetHealth/`.

Exemplos:

```bash
curl -s http://127.0.0.1:9501/api/v1/health/live
curl -s http://127.0.0.1:9501/api/v1/health/ready
```

---

### `POST` — `/api/v1/auth/register`

Registo de utilizador.

**Corpo JSON**

| Campo      | Tipo   | Regras                                                                                                 |
| ---------- | ------ | ------------------------------------------------------------------------------------------------------ |
| `name`     | string | obrigatório, 2–100 caracteres                                                                          |
| `email`    | string | obrigatório, email, máx. 255                                                                           |
| `password` | string | obrigatório, 8–128, `password_confirmation` igual, pelo menos uma minúscula, uma maiúscula e um dígito |

**Resposta 200**

```json
{
  "id": "<uuid>",
  "access_token": "<token>",
  "token_type": "Bearer",
  "message": "Registration successful.",
  "roles": ["user"],
  "permissions": []
}
```

**409** — Email já registado (`message` conforme excepção de domínio).

---

### `POST` — `/api/v1/auth/login`

**Corpo JSON**

| Campo      | Tipo   | Regras                       |
| ---------- | ------ | ---------------------------- |
| `email`    | string | obrigatório, email, máx. 255 |
| `password` | string | obrigatório, 1–255           |

**Resposta 200**

```json
{
  "access_token": "<token>",
  "token_type": "Bearer",
  "roles": ["user"],
  "permissions": []
}
```

**401** — Credenciais inválidas.

```json
{ "message": "Invalid email or password." }
```

---

### `POST` — `/api/v1/auth/logout`

API stateless: o token deixa de ser usado no cliente. O endpoint devolve uma mensagem informativa.

**Resposta 200**

```json
{
  "message": "Token discarded on the client. This endpoint is a no-op for stateless APIs."
}
```

---

### `POST` — `/api/v1/auth/forgot-password`

Pedido de reset (envio de código por email conforme configuração de mail).

**Corpo JSON**

| Campo   | Tipo   | Regras                       |
| ------- | ------ | ---------------------------- |
| `email` | string | obrigatório, email, máx. 255 |

**Resposta 200** — Mensagem genérica (não indica se o email existe).

```json
{
  "message": "If an account exists for that email, password reset instructions have been sent."
}
```

---

### `POST` — `/api/v1/auth/reset-password`

**Corpo JSON**

| Campo      | Tipo   | Regras                                                        |
| ---------- | ------ | ------------------------------------------------------------- |
| `code`     | string | obrigatório, exactamente 6 dígitos                            |
| `password` | string | mesmas regras fortes que no registo + `password_confirmation` |

**Resposta 200**

```json
{
  "message": "Password has been reset. You can sign in with your new password."
}
```

**422** — Código inválido ou expirado.

```json
{ "message": "Invalid or expired verification code." }
```

---

### `POST` — `/api/v1/auth/refresh` (autenticado)

**Cabeçalhos:** `Authorization: Bearer <token válido>`

**Resposta 200**

```json
{
  "access_token": "<novo_token>",
  "token_type": "Bearer",
  "roles": ["user"],
  "permissions": []
}
```

**401** — Token em falta, inválido, expirado, ou utilizador já não existe.

---

### `POST` — `/api/v1/auth/change-password` (autenticado)

**Cabeçalhos:** `Authorization: Bearer <token>`

**Corpo JSON**

| Campo              | Tipo   | Regras                                                        |
| ------------------ | ------ | ------------------------------------------------------------- |
| `current_password` | string | obrigatório, máx. 255                                         |
| `password`         | string | nova password, mesmas regras fortes + `password_confirmation` |

**Resposta 200**

```json
{ "message": "Password updated." }
```

**401** — Não autenticado ou password actual incorrecta.

---

### `GET` — `/api/v1/users/me` (autenticado)

**Cabeçalhos:** `Authorization: Bearer <token>` — requer `AuthenticateMiddleware` (token válido).

**Resposta 200**

```json
{
  "id": "<uuid>",
  "name": "...",
  "email": "...",
  "roles": ["user"],
  "permissions": []
}
```

**401** — Sem contexto de utilizador.  
**404** — Utilizador não encontrado na persistência.

---

### Admin — utilizadores (dashboard / backoffice)

| Método | Caminho | Permissões | Notas |
| ------ | ------- | ---------- | ----- |
| `GET` | `/api/v1/admin/users` | `users.view` | Query opcional: `page` (≥1), `per_page` (1–100, padrão 15), `search` (nome ou e-mail). Resposta: `data[]` com `id`, `name`, `email`, `created_at`, `updated_at` e `meta` (`total`, `page`, `per_page`, `last_page`). |
| `POST` | `/api/v1/admin/users` | `users.create` | Corpo: `name`, `email`, `password`, `password_confirmation` (mesmas regras que o registo público). Resposta **200** com `id` e mensagem; **409** se o e-mail existir. |
| `GET` | `/api/v1/admin/users/{id}` | `users.view` | Perfil com `roles` e `permissions` efectivas (slugs). **404** se o ID não existir. |
| `PUT` | `/api/v1/admin/users/{id}` | `users.update` | Corpo: `name`, `email`. **409** se o e-mail pertencer a outro utilizador. |

### Admin — papéis e permissões (RBAC)

Todas as rotas abaixo exigem `Authorization: Bearer` e permissões específicas (middleware `RequirePermissionsMiddleware`). Resposta **403** se o token for válido mas o utilizador não tiver **todas** as permissões exigidas pela rota.

| Método   | Caminho                                | Permissões                                                            |
| -------- | -------------------------------------- | --------------------------------------------------------------------- |
| `GET`    | `/api/v1/admin/roles`                  | `roles.view`                                                          |
| `POST`   | `/api/v1/admin/roles`                  | `roles.create` — corpo: `name`, `slug` (`^[a-z0-9_-]{1,64}$`)         |
| `DELETE` | `/api/v1/admin/roles/{id}`             | `roles.delete` — não elimina papéis com `is_system=true`              |
| `PUT`    | `/api/v1/admin/roles/{id}/permissions` | `roles.assign_permissions` — corpo: `{ "permission_slugs": ["..."] }` |
| `GET`    | `/api/v1/admin/permissions`            | `permissions.view`                                                    |
| `PUT`    | `/api/v1/admin/users/{id}/roles`       | `users.assign_roles` — corpo: `{ "role_slugs": ["admin","user"] }`    |

**Papéis iniciais** (após migração): `admin`, `manager`, `user`. Novos registos recebem o papel `user`. Para dar acesso de administração a um utilizador, atribua o papel `admin` (ou `manager`) via `PUT /admin/users/{id}/roles` com um token que já tenha `users.assign_roles`.

**Permissões seed** (slugs): `users.view`, `users.create`, `users.update`, `users.assign_roles`, `roles.view`, `roles.create`, `roles.delete`, `roles.assign_permissions`, `permissions.view`, `profile.view_roles`, `profile.view_permissions`. O papel `admin` tem todas; `manager` inclui gestão de utilizadores (`users.view`, `users.create`, `users.update`) e leitura de papéis/permissões; o papel `user` tem por omissão as permissões de perfil `profile.*` (ver documentação de `GET /users/me`).

---

### `GET` — `/api/v1/users/{id}`

Perfil público por ID (UUID).

**Resposta 200** — `id`, `name`, `email` (sem `roles` / `permissions`; estes campos existem em `GET /api/v1/users/me` para o utilizador autenticado).

**404**

```json
{ "message": "User not found" }
```

---

## Projetos (público)

### `GET` — `/api/v1/projects`

Lista projetos **publicados**, ordenados por `sort_order`.

Query: `page`, `per_page`.

### `GET` — `/api/v1/projects/{slug}`

Detalhe de um projeto publicado pelo slug.

### `GET` — `/api/v1/projects/{projectId}/posts`

Posts **publicados** de um projecto.

---

## Posts (público)

### `GET` — `/api/v1/posts/{id}`

Detalhe de um post publicado.

---

## Projetos (admin)

Requer autenticação + permissões RBAC (`projects.*`).

| Método | Rota | Permissão |
|--------|------|-----------|
| GET | `/api/v1/admin/projects` | `projects.view` |
| POST | `/api/v1/admin/projects` | `projects.create` |
| GET | `/api/v1/admin/projects/{id}` | `projects.view` |
| PUT | `/api/v1/admin/projects/{id}` | `projects.update` |
| DELETE | `/api/v1/admin/projects/{id}` | `projects.delete` |
| POST | `/api/v1/admin/projects/{id}/publish` | `projects.publish` |
| POST | `/api/v1/admin/projects/{id}/archive` | `projects.publish` |
| PUT | `/api/v1/admin/projects/reorder` | `projects.update` |

Corpo de criação: `title`, `slug?`, `description?`, `image_path?`, `owner_id?`.

---

## Posts (admin)

Requer autenticação + permissões RBAC (`posts.*`).

| Método | Rota | Permissão |
|--------|------|-----------|
| GET | `/api/v1/admin/projects/{projectId}/posts` | `posts.view` |
| POST | `/api/v1/admin/posts` | `posts.create` |
| GET | `/api/v1/admin/posts/{id}` | `posts.view` |
| PUT | `/api/v1/admin/posts/{id}` | `posts.update` |
| DELETE | `/api/v1/admin/posts/{id}` | `posts.delete` |
| POST | `/api/v1/admin/posts/{id}/publish` | `posts.publish` |

---

## Exemplos cURL

Substitua `HOST` (ex.: `http://127.0.0.1:9501`).

**Login**

```bash
curl -sS -X POST "$HOST/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"Secret123"}'
```

**Rota protegida (perfil)**

```bash
TOKEN="<colar_access_token>"

curl -sS "$HOST/api/v1/users/me" \
  -H "Authorization: Bearer $TOKEN"
```

**Refresh (com token ainda válido)**

```bash
curl -sS -X POST "$HOST/api/v1/auth/refresh" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

---

## Manutenção

Ao adicionar ou alterar rotas, actualize este ficheiro em conjunto com `config/routes.php` e os `FormRequest` correspondentes.
