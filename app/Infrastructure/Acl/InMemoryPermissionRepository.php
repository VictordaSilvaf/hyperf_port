<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Repository\PermissionRepositoryInterface;

final class InMemoryPermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private readonly InMemoryAclStore $store,
    ) {
    }

    public function all(): array
    {
        $list = array_values($this->store->permissionsById);
        usort($list, static fn (array $a, array $b) => strcmp($a['slug'], $b['slug']));

        return $list;
    }

    public function findIdBySlug(string $slug): ?string
    {
        return $this->store->permissionSlugToId[$slug] ?? null;
    }
}
