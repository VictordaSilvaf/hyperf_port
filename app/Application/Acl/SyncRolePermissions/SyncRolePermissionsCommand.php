<?php

declare(strict_types=1);

namespace App\Application\Acl\SyncRolePermissions;

final class SyncRolePermissionsCommand
{
    /**
     * @param list<string> $permissionSlugs
     */
    public function __construct(
        public readonly string $roleId,
        public readonly array $permissionSlugs,
    ) {
    }
}
