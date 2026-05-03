<?php

declare(strict_types=1);

namespace App\Domain\Acl\Repository;

interface RolePermissionWriterInterface
{
    /**
     * @param list<string> $permissionIds
     */
    public function syncForRole(string $roleId, array $permissionIds): void;
}
