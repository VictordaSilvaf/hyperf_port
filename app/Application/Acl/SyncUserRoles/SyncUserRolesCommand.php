<?php

declare(strict_types=1);

namespace App\Application\Acl\SyncUserRoles;

final class SyncUserRolesCommand
{
    /**
     * @param list<string> $roleSlugs
     */
    public function __construct(
        public readonly string $userId,
        public readonly array $roleSlugs,
    ) {
    }
}
