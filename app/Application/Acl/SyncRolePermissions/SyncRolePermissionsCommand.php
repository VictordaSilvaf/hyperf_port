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

namespace App\Application\Acl\SyncRolePermissions;

final class SyncRolePermissionsCommand
{
    /**
     * @param list<string> $permissionSlugs
     */
    public function __construct(
        public readonly string $roleId,
        public readonly array $permissionSlugs,
    ) {
    }
}
