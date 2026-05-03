<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Repository\RolePermissionWriterInterface;

final class InMemoryRolePermissionWriter implements RolePermissionWriterInterface
{
    public function __construct(
        private readonly InMemoryAclStore $store,
    ) {
    }

    public function syncForRole(string $roleId, array $permissionIds): void
    {
        $this->store->roleToPermissionIds[$roleId] = array_values($permissionIds);
    }
}
