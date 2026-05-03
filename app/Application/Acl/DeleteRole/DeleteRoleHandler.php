<?php

declare(strict_types=1);

namespace App\Application\Acl\DeleteRole;

use App\Domain\Acl\Repository\RoleRepositoryInterface;
use InvalidArgumentException;

final class DeleteRoleHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
    ) {
    }

    public function handle(string $roleId): void
    {
        $role = $this->roles->findById($roleId);
        if ($role === null) {
            throw new InvalidArgumentException('Role not found.');
        }
        if ($role->isSystem()) {
            throw new InvalidArgumentException('Cannot delete a system role.');
        }

        $this->roles->deleteById($roleId);
    }
}
