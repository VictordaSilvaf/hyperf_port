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

namespace App\Infrastructure\Persistence\Acl;

use App\Application\Acl\EffectivePermissionsProviderInterface;
use Hyperf\DbConnection\Db;

final class DbEffectivePermissionsProvider implements EffectivePermissionsProviderInterface
{
    public function permissionSlugsForUser(string $userId): array
    {
        $rows = Db::table('user_role as ur')
            ->join('role_permission as rp', 'ur.role_id', '=', 'rp.role_id')
            ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
            ->where('ur.user_id', $userId)
            ->distinct()
            ->pluck('p.slug');

        return array_values(array_map(static fn ($s) => (string) $s, $rows->all()));
    }

    public function roleSlugsForUser(string $userId): array
    {
        $rows = Db::table('user_role as ur')
            ->join('roles as r', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', $userId)
            ->orderBy('r.slug')
            ->pluck('r.slug');

        return array_values(array_map(static fn ($s) => (string) $s, $rows->all()));
    }
}
