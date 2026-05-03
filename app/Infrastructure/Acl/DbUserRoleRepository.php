<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Repository\UserRoleRepositoryInterface;
use Hyperf\DbConnection\Db;

final class DbUserRoleRepository implements UserRoleRepositoryInterface
{
    private const TABLE = 'user_role';

    public function getRoleIdsForUser(string $userId): array
    {
        return Db::table(self::TABLE)
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->map(static fn ($id) => (string) $id)
            ->all();
    }

    public function syncRoleIdsForUser(string $userId, array $roleIds): void
    {
        Db::table(self::TABLE)->where('user_id', $userId)->delete();
        foreach ($roleIds as $roleId) {
            Db::table(self::TABLE)->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function addRoleSlugToUserIfMissing(string $userId, string $roleSlug): void
    {
        $roleId = Db::table('roles')->where('slug', $roleSlug)->value('id');
        if ($roleId === null) {
            return;
        }
        Db::table(self::TABLE)->insertOrIgnore([
            'user_id' => $userId,
            'role_id' => (string) $roleId,
        ]);
    }
}
