<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Entity\Role;

/**
 * Shared in-memory state for ACL when APP_USER_REPOSITORY=memory (tests / dev).
 */
final class InMemoryAclStore
{
    public const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    public const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    public const ROLE_USER = 'a0000003-0000-4000-8000-000000000001';

    /** @var array<string, Role> id => Role */
    public array $rolesById = [];

    /** @var array<string, string> slug => id */
    public array $roleSlugToId = [];

    /** @var array<string, array{id: string, slug: string, description: ?string}> */
    public array $permissionsById = [];

    /** @var array<string, string> slug => id */
    public array $permissionSlugToId = [];

    /** @var array<string, list<string>> roleId => permission ids */
    public array $roleToPermissionIds = [];

    /** @var array<string, list<string>> userId => role ids */
    public array $userToRoleIds = [];

    public static function seeded(): self
    {
        $s = new self();
        $roles = [
            Role::restore(self::ROLE_ADMIN, 'admin', 'Administrador', true),
            Role::restore(self::ROLE_MANAGER, 'manager', 'Gestor', true),
            Role::restore(self::ROLE_USER, 'user', 'Utilizador', true),
        ];
        foreach ($roles as $r) {
            $s->rolesById[$r->id()] = $r;
            $s->roleSlugToId[$r->slug()] = $r->id();
        }

        $perms = [
            ['id' => 'b0000001-0000-4000-8000-000000000001', 'slug' => 'users.view', 'description' => 'Ver utilizadores'],
            ['id' => 'b0000002-0000-4000-8000-000000000001', 'slug' => 'users.assign_roles', 'description' => 'Atribuir papéis'],
            ['id' => 'b0000003-0000-4000-8000-000000000001', 'slug' => 'roles.view', 'description' => 'Listar papéis'],
            ['id' => 'b0000004-0000-4000-8000-000000000001', 'slug' => 'roles.create', 'description' => 'Criar papéis'],
            ['id' => 'b0000005-0000-4000-8000-000000000001', 'slug' => 'roles.delete', 'description' => 'Eliminar papéis'],
            ['id' => 'b0000006-0000-4000-8000-000000000001', 'slug' => 'roles.assign_permissions', 'description' => 'Permissões do papel'],
            ['id' => 'b0000007-0000-4000-8000-000000000001', 'slug' => 'permissions.view', 'description' => 'Listar permissões'],
        ];
        foreach ($perms as $p) {
            $s->permissionsById[$p['id']] = $p;
            $s->permissionSlugToId[$p['slug']] = $p['id'];
        }

        $allPermIds = array_column($perms, 'id');
        $s->roleToPermissionIds[self::ROLE_ADMIN] = $allPermIds;
        $s->roleToPermissionIds[self::ROLE_MANAGER] = [
            'b0000001-0000-4000-8000-000000000001',
            'b0000003-0000-4000-8000-000000000001',
            'b0000007-0000-4000-8000-000000000001',
        ];
        $s->roleToPermissionIds[self::ROLE_USER] = [];

        return $s;
    }
}
