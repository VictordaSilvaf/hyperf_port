<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */

namespace App\Application\Acl\SyncRolePermissions;

use App\Domain\Acl\Repository\PermissionRepositoryInterface;
use App\Domain\Acl\Repository\RolePermissionWriterInterface;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use InvalidArgumentException;

final class SyncRolePermissionsHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionRepositoryInterface $permissions,
        private readonly RolePermissionWriterInterface $rolePermissions,
    ) {
    }

    public function handle(SyncRolePermissionsCommand $command): void
    {
        if ($this->roles->findById($command->roleId) === null) {
            throw new InvalidArgumentException('Role not found.');
        }

        $ids = [];
        foreach ($command->permissionSlugs as $slug) {
            $id = $this->permissions->findIdBySlug($slug);
            if ($id === null) {
                throw new InvalidArgumentException(sprintf('Unknown permission: %s', $slug));
            }
            $ids[] = $id;
        }

        $this->rolePermissions->syncForRole($command->roleId, $ids);
    }
}
