<?php

declare(strict_types=1);

namespace App\Domain\Acl\Repository;

use App\Domain\Acl\Entity\Role;

interface RoleRepositoryInterface
{
    /** @return list<Role> */
    public function all(): array;

    public function findById(string $id): ?Role;

    public function findBySlug(string $slug): ?Role;

    public function save(Role $role): void;

    public function deleteById(string $id): void;
}
