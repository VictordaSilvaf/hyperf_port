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

namespace App\Application\Acl\CreateRole;

use App\Domain\Acl\Entity\Role;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use InvalidArgumentException;

final class CreateRoleHandler
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
    ) {
    }

    public function handle(CreateRoleCommand $command): string
    {
        if ($this->roles->findBySlug($command->slug) !== null) {
            throw new InvalidArgumentException('Role slug already exists.');
        }

        $role = Role::create($command->name, $command->slug);
        $this->roles->save($role);

        return $role->id();
    }
}
