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

namespace App\Domain\Acl\Repository;

interface RolePermissionWriterInterface
{
    /**
     * @param list<string> $permissionIds
     */
    public function syncForRole(string $roleId, array $permissionIds): void;
}
