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

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Entity\Role;
use App\Domain\Acl\Repository\RoleRepositoryInterface;

final class InMemoryRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly InMemoryAclStore $store,
    ) {
    }

    public function all(): array
    {
        $list = array_values($this->store->rolesById);
        usort($list, static fn (Role $a, Role $b) => strcmp($a->slug(), $b->slug()));

        return $list;
    }

    public function findById(string $id): ?Role
    {
        return $this->store->rolesById[$id] ?? null;
    }

    public function findBySlug(string $slug): ?Role
    {
        $id = $this->store->roleSlugToId[$slug] ?? null;
        if ($id === null) {
            return null;
        }

        return $this->store->rolesById[$id] ?? null;
    }

    public function save(Role $role): void
    {
        $this->store->rolesById[$role->id()] = $role;
        $this->store->roleSlugToId[$role->slug()] = $role->id();
        if (! isset($this->store->roleToPermissionIds[$role->id()])) {
            $this->store->roleToPermissionIds[$role->id()] = [];
        }
    }

    public function deleteById(string $id): void
    {
        $role = $this->store->rolesById[$id] ?? null;
        if ($role === null) {
            return;
        }
        unset($this->store->rolesById[$id], $this->store->roleSlugToId[$role->slug()], $this->store->roleToPermissionIds[$id]);
        foreach ($this->store->userToRoleIds as $uid => $roleIds) {
            $this->store->userToRoleIds[$uid] = array_values(array_filter($roleIds, static fn (string $rid) => $rid !== $id));
        }
    }
}
