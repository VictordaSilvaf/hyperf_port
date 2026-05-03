<?php

declare(strict_types=1);

namespace App\Domain\Acl\Repository;

interface PermissionRepositoryInterface
{
    /**
     * @return list<array{id: string, slug: string, description: ?string}>
     */
    public function all(): array;

    public function findIdBySlug(string $slug): ?string;
}
