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

namespace App\Application\Acl\SyncUserRoles;

use App\Domain\Acl\Repository\RoleRepositoryInterface;
use App\Domain\Acl\Repository\UserRoleRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;
use InvalidArgumentException;

final class SyncUserRolesHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
        private readonly UserRoleRepositoryInterface $userRoles,
    ) {
    }

    public function handle(SyncUserRolesCommand $command): void
    {
        if ($this->users->findById(UserId::fromString($command->userId)) === null) {
            throw new InvalidArgumentException('User not found.');
        }

        $roleIds = [];
        foreach ($command->roleSlugs as $slug) {
            $role = $this->roles->findBySlug($slug);
            if ($role === null) {
                throw new InvalidArgumentException(sprintf('Unknown role: %s', $slug));
            }
            $roleIds[] = $role->id();
        }

        $this->userRoles->syncRoleIdsForUser($command->userId, $roleIds);
    }
}
