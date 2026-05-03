# Referência da API HTTP

Todas as rotas documentadas abaixo estão registadas em `config/routes.php` dentro do grupo com prefixo **`/api`**.

**Base path:** `/api`  
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

A configuração `debug` em `config/config.php` reflecte `APP_DEBUG`. Quando `APP_DEBUG=true`, respostas **500** podem incluir `message` detalhada, `exception`, `file`, `line` e `trace`. Em produção, mantém-se uma mensagem genérica (`Internal Server Error.`). Ver `app/Exception/Handler/AppExceptionHandler.php`.

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

`POST /api/auth/refresh` só funciona enquanto o token enviado no `Authorization` **ainda é válido** (assinatura correcta e `exp` no futuro). Para prolongar sessões no cliente, chame refresh **antes** da expiração. Se o token já expirou, é necessário voltar a fazer login.

---

## Rotas

### `GET` / `POST` / `HEAD` — `/api/`

Health / exemplo de index.

| Query | Obrigatório | Descrição |
|-------|-------------|-----------|
| `user` | Não | Nome a incluir na mensagem (por omissão `Hyperf`) |

**Resposta 200**

```json
{
  "method": "GET",
  "message": "Hello Hyperf."
}
```

Controlador: `app/Controller/IndexController.php`.

---

### `POST` — `/api/auth/register`

Registo de utilizador.

**Corpo JSON**

| Campo | Tipo | Regras |
|-------|------|--------|
| `name` | string | obrigatório, 2–100 caracteres |
| `email` | string | obrigatório, email, máx. 255 |
| `password` | string | obrigatório, 8–128, `password_confirmation` igual, pelo menos uma minúscula, uma maiúscula e um dígito |

**Resposta 200**

```json
{
  "id": "<uuid>",
  "access_token": "<token>",
  "token_type": "Bearer",
  "message": "Registration successful."
}
```

**409** — Email já registado (`message` conforme excepção de domínio).

---

### `POST` — `/api/auth/login`

**Corpo JSON**

| Campo | Tipo | Regras |
|-------|------|--------|
| `email` | string | obrigatório, email, máx. 255 |
| `password` | string | obrigatório, 1–255 |

**Resposta 200**

```json
{
  "access_token": "<token>",
  "token_type": "Bearer"
}
```

**401** — Credenciais inválidas.

```json
{ "message": "Invalid email or password." }
```

---

### `POST` — `/api/auth/logout`

API stateless: o token deixa de ser usado no cliente. O endpoint devolve uma mensagem informativa.

**Resposta 200**

```json
{
  "message": "Token discarded on the client. This endpoint is a no-op for stateless APIs."
}
```

---

### `POST` — `/api/auth/forgot-password`

Pedido de reset (envio de código por email conforme configuração de mail).

**Corpo JSON**

| Campo | Tipo | Regras |
|-------|------|--------|
| `email` | string | obrigatório, email, máx. 255 |

**Resposta 200** — Mensagem genérica (não indica se o email existe).

```json
{
  "message": "If an account exists for that email, password reset instructions have been sent."
}
```

---

### `POST` — `/api/auth/reset-password`

**Corpo JSON**

| Campo | Tipo | Regras |
|-------|------|--------|
| `code` | string | obrigatório, exactamente 6 dígitos |
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

### `POST` — `/api/auth/refresh` (autenticado)

**Cabeçalhos:** `Authorization: Bearer <token válido>`

**Resposta 200**

```json
{
  "access_token": "<novo_token>",
  "token_type": "Bearer"
}
```

**401** — Token em falta, inválido, expirado, ou utilizador já não existe.

---

### `POST` — `/api/auth/change-password` (autenticado)

**Cabeçalhos:** `Authorization: Bearer <token>`

**Corpo JSON**

| Campo | Tipo | Regras |
|-------|------|--------|
| `current_password` | string | obrigatório, máx. 255 |
| `password` | string | nova password, mesmas regras fortes + `password_confirmation` |

**Resposta 200**

```json
{ "message": "Password updated." }
```

**401** — Não autenticado ou password actual incorrecta.

---

### `GET` — `/api/me` (autenticado)

**Cabeçalhos:** `Authorization: Bearer <token>`

**Resposta 200**

```json
{
  "id": "<uuid>",
  "name": "...",
  "email": "..."
}
```

**401** — Sem contexto de utilizador.  
**404** — Utilizador não encontrado na persistência.

---

### `GET` — `/api/users/{id}`

Perfil público por ID (UUID).

**Resposta 200** — Mesmo formato que `/api/me`.

**404**

```json
{ "message": "User not found" }
```

---

## Exemplos cURL

Substitua `HOST` (ex.: `http://127.0.0.1:9501`).

**Login**

```bash
curl -sS -X POST "$HOST/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"Secret123"}'
```

**Rota protegida (perfil)**

```bash
TOKEN="<colar_access_token>"

curl -sS "$HOST/api/me" \
  -H "Authorization: Bearer $TOKEN"
```

**Refresh (com token ainda válido)**

```bash
curl -sS -X POST "$HOST/api/auth/refresh" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

---

## Manutenção

Ao adicionar ou alterar rotas, actualize este ficheiro em conjunto com `config/routes.php` e os `FormRequest` correspondentes.
