# AGENTS.md — Hyperf API (DDD / Hexagonal)

Instruções para agentes de código (Cursor, Copilot, Claude Code, etc.) neste repositório.

## Visão geral

API REST em **Hyperf 3.x** (PHP ≥ 8.4) com camadas **Domain → Application → Infrastructure**, autenticação Bearer (HMAC), RBAC e testes com **Pest**.

| Documento | Conteúdo |
|-----------|----------|
| [README.md](README.md) | Setup, Docker, variáveis de ambiente |
| [docs/PROJECT.md](docs/PROJECT.md) | Arquitetura, dependências entre camadas, convenções |
| [docs/API.md](docs/API.md) | Rotas, payloads, códigos HTTP, exemplos |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Git Flow, Conventional Commits, hooks e PR |

## Comandos essenciais

Tudo no **container** via CLI `hyper` (ver README). Primeira vez: `./hyper install`.

```bash
hyper up -d
hyper migrate
hyper restart          # após alterar rotas/config/.env
hyper shell
hyper test             # Pest + Swoole (suíte completa)
hyper quality          # lint + PHPStan + Pest
composer setup:hooks   # Git hooks (corre no host)
```

## Git Flow e commits

- Branches: `main`, `develop`, `feature/*`, `bugfix/*`, `hotfix/*`, `release/*`
- Commits: **Conventional Commits** — ver regra `.cursor/rules/commit-messages.mdc` e `CONTRIBUTING.md`
- Formato: `type(scope): subject` — subject minúsculo, sem ponto, máx. 100 chars
- Ver [CONTRIBUTING.md](CONTRIBUTING.md); não commitar sem pedido explícito do utilizador

## Regras de arquitetura (resumo)

```
Controller → Application (Handler) → Domain + Ports (interfaces)
Infrastructure implementa ports; Domain não importa Hyperf/DB/Redis.
```

- Novo caso de uso: `Application/<Contexto>/<Nome>/{Command|Query,Handler}`
- Novo endpoint: rota em `config/routes.php` + controller fino + `FormRequest`
- Registar implementações em `config/autoload/dependencies.php`
- Mensagens HTTP: `trans('http.*')`; validação: `storage/languages/{locale}/`

## Ao implementar mudanças

1. Respeitar limites de camada — ver [docs/PROJECT.md](docs/PROJECT.md)
2. Alterou rotas ou contratos → actualizar [docs/API.md](docs/API.md)
3. Comportamento novo → testes em `test/Unit/` ou `test/Cases/`
4. Migrações em `migrations/` com timestamp consistente
5. Não commitar `.env`, credenciais ou segredos
6. Preferir diffs mínimos; seguir estilo existente (PHP-CS-Fixer, `declare(strict_types=1)`)

## Ambiente e locale

- Locale da API: `APP_LOCALE=pt_BR` (fallback `en`)
- Testes PHPUnit usam `APP_LOCALE=en` (`phpunit.xml.dist`)
- `APP_USER_REPOSITORY`: `memory` (dev) ou `db` (MySQL)
- Object storage: `FILESYSTEM_DRIVER=minio` (dev) / `r2` (produção); porta `ObjectStorageInterface`
- Prefixo global da API: `/api/v1`

## Segurança

- Nunca expor `APP_AUTH_SECRET` ou credenciais de seed em produção
- `APP_DEBUG=false` em produção
- Tokens e reset de password: respeitar TTLs em `.env.example`
