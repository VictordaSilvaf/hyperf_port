<?php

declare(strict_types=1);

namespace App\Domain\Acl\Repository;

interface UserRoleRepositoryInterface
{
    /** @return list<string> role ids */
    public function getRoleIdsForUser(string $userId): array;

    /**
     * Replace all roles for the user with the given role ids.
     *
     * @param list<string> $roleIds
     */
    public function syncRoleIdsForUser(string $userId, array $roleIds): void;

    public function addRoleSlugToUserIfMissing(string $userId, string $roleSlug): void;
}
