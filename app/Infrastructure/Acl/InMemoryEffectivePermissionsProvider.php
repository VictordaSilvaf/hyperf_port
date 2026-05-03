<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Application\Acl\EffectivePermissionsProviderInterface;

final class InMemoryEffectivePermissionsProvider implements EffectivePermissionsProviderInterface
{
    public function __construct(
        private readonly InMemoryAclStore $store,
    ) {
    }

    public function permissionSlugsForUser(string $userId): array
    {
        $roleIds = $this->store->userToRoleIds[$userId] ?? [];
        $permIds = [];
        foreach ($roleIds as $rid) {
            foreach ($this->store->roleToPermissionIds[$rid] ?? [] as $pid) {
                $permIds[$pid] = true;
            }
        }
        $slugs = [];
        foreach (array_keys($permIds) as $pid) {
            $row = $this->store->permissionsById[$pid] ?? null;
            if ($row !== null) {
                $slugs[$row['slug']] = true;
            }
        }

        return array_keys($slugs);
    }

    public function roleSlugsForUser(string $userId): array
    {
        $roleIds = $this->store->userToRoleIds[$userId] ?? [];
        $slugs = [];
        foreach ($roleIds as $rid) {
            $role = $this->store->rolesById[$rid] ?? null;
            if ($role !== null) {
                $slugs[] = $role->slug();
            }
        }
        sort($slugs);

        return $slugs;
    }
}
