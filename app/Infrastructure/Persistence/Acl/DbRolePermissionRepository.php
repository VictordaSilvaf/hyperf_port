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

use App\Domain\Acl\Repository\RolePermissionWriterInterface;
use Hyperf\DbConnection\Db;

final class DbRolePermissionRepository implements RolePermissionWriterInterface
{
    private const TABLE = 'role_permission';

    /**
     * @param list<string> $permissionIds
     */
    public function syncForRole(string $roleId, array $permissionIds): void
    {
        Db::table(self::TABLE)->where('role_id', $roleId)->delete();
        foreach ($permissionIds as $permissionId) {
            Db::table(self::TABLE)->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }
}
