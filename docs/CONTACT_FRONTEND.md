# Guia de implementação — Contacto (frontend)

Este guia descreve como integrar a página de contacto e o inbox admin no frontend (Next.js, React, etc.).

## Visão geral

| Recurso | Endpoint | Auth |
|---------|----------|------|
| Página CMS | `GET /api/v1/pages/contato` | Não |
| Dados estáticos (email, telefone) | `GET /api/v1/site/settings` → `data.contact` | Não |
| Envio do formulário | `POST /api/v1/contact` | Não |
| Inbox admin | `GET /api/v1/admin/contact/messages` | Bearer + `contact.view` |
| Detalhe | `GET /api/v1/admin/contact/messages/{id}` | Bearer + `contact.view` |
| Arquivar | `PATCH /api/v1/admin/contact/messages/{id}` | Bearer + `contact.update` |

Base URL: `/api/v1` (ex.: `https://api.victordev.com/api/v1`).

---

## 1. Página pública `/contato`

### 1.1 Carregar conteúdo CMS

```http
GET /api/v1/pages/contato
```

A resposta inclui `data.blocks`. Procure o bloco `type === "contact_form"`:

```json
{
  "type": "contact_form",
  "payload": {
    "title": "Formulário de contacto",
    "subtitle": null,
    "submit_label": "Enviar mensagem",
    "success_message": "Mensagem enviada com sucesso!",
    "show_subject": true
  }
}
```

Use `payload` para labels e mensagem de sucesso local. O bloco **não** envia o formulário — apenas configura a UI.

### 1.2 Dados de contacto estáticos

```http
GET /api/v1/site/settings
```

```json
{
  "data": {
    "contact": {
      "email": "hello@victordev.com",
      "phone": "+351 900 000 000",
      "whatsapp": "https://wa.me/351900000000",
      "address": { "line1": "...", "city": "Lisboa", "country": "PT" },
      "notification_email": null
    }
  }
}
```

Exiba `email`, `phone`, `whatsapp` e `address` na sidebar ou secção lateral. `notification_email` é só para o backend (destino do email).

---

## 2. Formulário de envio

### 2.1 Campos

| Campo HTML | Nome no POST | Obrigatório |
|------------|--------------|-------------|
| Nome | `name` | Sim |
| Email | `email` | Sim |
| Assunto | `subject` | Só se `show_subject === true` no bloco |
| Mensagem | `message` | Sim (min. 10 caracteres) |
| Honeypot (oculto) | `website` | Deve ficar **vazio** — não enviar valor |
| Cloudflare Turnstile | `cf_turnstile_response` | Sim em produção se `TURNSTILE_ENABLED=true` |

### 2.2 Exemplo (fetch)

```typescript
async function submitContact(form: {
  name: string;
  email: string;
  subject?: string;
  message: string;
  turnstileToken?: string;
}) {
  const res = await fetch(`${API_URL}/api/v1/contact`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({
      name: form.name,
      email: form.email,
      subject: form.subject ?? null,
      message: form.message,
      // website: omitir ou não enviar (honeypot)
      cf_turnstile_response: form.turnstileToken ?? '',
    }),
  });

  const data = await res.json();
  // Sempre 200 com mensagem genérica em caso de sucesso de validação
  return data.message;
}
```

### 2.3 Honeypot

```html
<!-- CSS: display:none ou position:absolute; left:-9999px -->
<input type="text" name="website" tabIndex={-1} autoComplete="off" />
```

Se o utilizador (ou bot) preencher `website`, a API responde **422**.

### 2.4 Cloudflare Turnstile (recomendado em produção)

1. Criar widget no [Cloudflare Dashboard](https://dash.cloudflare.com/) → Turnstile.
2. Configurar no backend: `TURNSTILE_ENABLED=true`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`.
3. No frontend:

```tsx
import { Turnstile } from '@marsidev/react-turnstile';

<Turnstile
  siteKey={process.env.NEXT_PUBLIC_TURNSTILE_SITE_KEY!}
  onSuccess={(token) => setTurnstileToken(token)}
/>
```

Envie o token em `cf_turnstile_response`. Se o captcha falhar no servidor, a API ainda responde **200** com a mesma mensagem genérica (a mensagem **não** é guardada).

### 2.5 UX após envio

- Mostrar `payload.success_message` do bloco ou a `message` da API.
- Limpar o formulário.
- Não distinguir “email inválido no servidor” vs “sucesso” na UI pública.

---

## 3. Admin — inbox de mensagens

Requer login admin (`POST /api/v1/auth/login`) e permissões `contact.view` / `contact.update`.

### 3.1 Listagem

```http
GET /api/v1/admin/contact/messages?page=1&per_page=15&status=new
Authorization: Bearer <token>
```

```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Jane",
      "email": "jane@example.com",
      "subject": "Projeto",
      "status": "new",
      "created_at": "2026-07-07T12:00:00+00:00"
    }
  ],
  "meta": { "total": 1, "page": 1, "per_page": 15 }
}
```

Filtros de `status`: `new`, `read`, `archived`.

### 3.2 Detalhe (marca como lida)

```http
GET /api/v1/admin/contact/messages/{id}
```

Resposta inclui `body`, `ip_address`, `user_agent`. Se `status` era `new`, passa a `read` automaticamente.

### 3.3 Arquivar

```http
PATCH /api/v1/admin/contact/messages/{id}
Content-Type: application/json

{ "status": "archived" }
```

Também aceita `{ "status": "read" }`.

---

## 4. Fluxo recomendado (diagrama)

```
[Utilizador] → GET /pages/contato + GET /site/settings
            → Render blocos + contact info
            → Preenche form + Turnstile
            → POST /contact
            → Mostra success_message

[Admin] → GET /admin/contact/messages?status=new
        → GET /admin/contact/messages/{id}  (lê + marca read)
        → PATCH status=archived
```

---

## 5. Variáveis de ambiente

### Backend (`.env`)

| Variável | Descrição |
|----------|-----------|
| `MAIL_CONTACT_TO` | Fallback do email de notificação |
| `MAIL_CONTACT_SUBJECT` | Assunto padrão do email |
| `TURNSTILE_ENABLED` | `true` em produção |
| `TURNSTILE_SECRET_KEY` | Segredo server-side |
| `TURNSTILE_SITE_KEY` | Chave pública (também no frontend) |

### Frontend

| Variável | Descrição |
|----------|-----------|
| `NEXT_PUBLIC_API_URL` | Base da API |
| `NEXT_PUBLIC_TURNSTILE_SITE_KEY` | Site key Turnstile |

---

## 6. Publicar a página `contato`

A migration cria a página em **draft**. No admin:

1. `GET /api/v1/admin/pages` — encontrar slug `contato`
2. `PATCH /api/v1/admin/pages/{id}/publish` — publicar
3. Opcional: `PUT /api/v1/admin/site/settings` — configurar `contact.notification_email`

---

## 7. Testes locais

- Mailpit: `http://localhost:8025` (emails de notificação)
- Postman: pasta **03 — Portfolio** (`Contact — submit`) e **10 — Admin — Contact**
- `./hyper migrate` antes de testar com PostgreSQL
