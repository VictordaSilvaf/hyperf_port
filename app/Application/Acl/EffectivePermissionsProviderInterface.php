<?php

declare(strict_types=1);

namespace App\Application\Acl;

interface EffectivePermissionsProviderInterface
{
    /**
     * @return list<string> distinct permission slugs
     */
    public function permissionSlugsForUser(string $userId): array;

    /**
     * @return list<string> role slugs
     */
    public function roleSlugsForUser(string $userId): array;
}
