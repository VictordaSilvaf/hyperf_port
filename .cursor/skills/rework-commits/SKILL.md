# Split branch into reviewable commits

Rework a branch into a sequence of small, semantic commits for review.

## Important

- Prepend `GIT_EDITOR=true` to all git commands you run, especially ones looking at diffs, so you avoid getting blocked
- **Never alter behaviour or architecture** while splitting — only reorganise existing changes into cleaner commits
- Read and follow the project rules before planning: `.cursor/rules/project-core.mdc`, `.cursor/rules/ddd-architecture.mdc`, `.cursor/rules/api-endpoints.mdc`, `.cursor/rules/migrations.mdc`, `.cursor/rules/commit-messages.mdc`, and `docs/PROJECT.md`

## Project architecture (mandatory)

This is a **Hyperf 3.x API** with **DDD / hexagonal** layers. Every commit must respect these boundaries — do not introduce shortcuts, cross-layer leaks, or patterns that contradict the rules.

### Layer flow

```
Controller → Application (Handler) → Domain + Ports (interfaces)
Infrastructure implements ports; Domain must NOT import Hyperf, DB, or Redis.
```

| Layer | Location | May depend on |
|-------|----------|---------------|
| Domain | `app/Domain/{Contexto}/` | Domain + PHP only |
| Application | `app/Application/{Contexto}/` | Domain + port interfaces |
| Infrastructure | `app/Infrastructure/` | Domain, Application, Hyperf, drivers |
| Presentation | `app/Presentation/Http/` | Application, Domain exceptions, auth infra |

### Conventions to preserve in every commit

- **Use case** = one folder: `Application/<Contexto>/<Nome>/{Command\|Query,Handler}`; handler receives dependencies via constructor (interfaces)
- **HTTP endpoint** = route in `config/routes.php` + thin controller + `FormRequest` + `Resource` when exposed; business logic stays in handlers
- **Persistence** = implement port in `Infrastructure/Persistence/` and register in `config/autoload/dependencies.php`
- **Messages** = `trans('http.*')` for HTTP; validation strings in `storage/languages/{locale}/`
- **Migrations** = `YYYY_MM_DD_HHMMSS_descricao.php`; RBAC permissions follow existing migration patterns
- **Tests** = handler unit tests in `test/Unit/`; feature tests in `test/Cases/`
- **Docs** = route/contract changes include `docs/API.md` in the same logical commit
- **Style** = `declare(strict_types=1)`, minimal diffs, reuse existing patterns — no new abstractions unless the original diff already introduced them

### Forbidden (abort or fix before committing)

- Domain importing Hyperf, Redis, mail, or concrete Infrastructure
- Application instantiating concrete repositories instead of port interfaces
- Business logic in controllers
- Committing `.env`, credentials, or secrets
- Commit messages outside Conventional Commits (`type(scope): subject`, max 100 chars — see `.cursor/rules/commit-messages.mdc`)

## Instructions

1. **Check for uncommitted changes**: Abort if there are any.
2. **Check rebase status**: Verify the branch is rebased on top of `main`. Abort if not.
3. **Save recovery point**: Tell the user the current commit hash in case we need to `git reset --hard` to it later.
4. **Save the original diff**: Save the full git diff to `/tmp/original-diff.patch` before making changes.
5. **Reset to main**: Run `git reset main` to unstage all changes.
6. **Plan the commits**: Read through ALL changes carefully. Plan a logical breakdown into small, sequential, semantic commits. Write a TODO for each in `/tmp/split-todos.md`. Order commits by dependency and architecture:
   - **Migrations / schema** first
   - **Domain** (entities, VOs, exceptions, ports)
   - **Application** (commands/queries + handlers)
   - **Infrastructure** (persistence, storage, etc.) + `dependencies.php` bindings
   - **Presentation** (routes, controllers, requests, resources, middleware)
   - **Tests** aligned with the layer they cover (prefer same commit as the feature when small)
   - **Docs** (`docs/API.md`, etc.) with the contract they describe
   - **CI / build / chore** last
7. **Create the commits**: Work through the TODOs one by one. Each commit must be self-contained, compile logically in isolation, and respect layer boundaries. Use Conventional Commits; explain the **why** for reviewers.
8. **Validate**:
   - Compare the current diff against `/tmp/original-diff.patch` to ensure no changes were lost or altered
   - Confirm no commit violates the architecture rules above (layer imports, controller thickness, port usage)
9. **Cleanup**: Delete temporary files once validation passes.

## Notes

- If validation fails, tell the user and provide the original commit hash for recovery
- Each commit should be self-contained and represent a logical unit of work
- Commit messages should explain the "why" behind the changes
- When in doubt about layer placement or commit order, consult `docs/PROJECT.md` and mirror how similar features are already structured in the codebase
