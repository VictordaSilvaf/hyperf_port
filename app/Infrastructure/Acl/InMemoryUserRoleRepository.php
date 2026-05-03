<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Repository\RoleRepositoryInterface;
use App\Domain\Acl\Repository\UserRoleRepositoryInterface;

final class InMemoryUserRoleRepository implements UserRoleRepositoryInterface
{
    public function __construct(
        private readonly InMemoryAclStore $store,
        private readonly RoleRepositoryInterface $roles,
    ) {
    }

    public function getRoleIdsForUser(string $userId): array
    {
        return $this->store->userToRoleIds[$userId] ?? [];
    }

    public function syncRoleIdsForUser(string $userId, array $roleIds): void
    {
        $this->store->userToRoleIds[$userId] = array_values(array_unique($roleIds));
    }

    public function addRoleSlugToUserIfMissing(string $userId, string $roleSlug): void
    {
        $role = $this->roles->findBySlug($roleSlug);
        if ($role === null) {
            return;
        }
        $current = $this->store->userToRoleIds[$userId] ?? [];
        if (! in_array($role->id(), $current, true)) {
            $current[] = $role->id();
            $this->store->userToRoleIds[$userId] = $current;
        }
    }
}
