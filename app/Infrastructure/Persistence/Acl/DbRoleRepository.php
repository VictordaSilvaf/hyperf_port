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

use App\Domain\Acl\Entity\Role;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use Hyperf\DbConnection\Db;

final class DbRoleRepository implements RoleRepositoryInterface
{
    private const TABLE = 'roles';

    public function all(): array
    {
        $rows = Db::table(self::TABLE)->orderBy('slug')->get();
        $out = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $out[] = Role::restore(
                (string) $data['id'],
                (string) $data['slug'],
                (string) $data['name'],
                (bool) $data['is_system'],
            );
        }

        return $out;
    }

    public function findById(string $id): ?Role
    {
        $row = Db::table(self::TABLE)->where('id', $id)->first();
        if ($row === null) {
            return null;
        }
        $data = (array) $row;

        return Role::restore(
            (string) $data['id'],
            (string) $data['slug'],
            (string) $data['name'],
            (bool) $data['is_system'],
        );
    }

    public function findBySlug(string $slug): ?Role
    {
        $row = Db::table(self::TABLE)->where('slug', $slug)->first();
        if ($row === null) {
            return null;
        }
        $data = (array) $row;

        return Role::restore(
            (string) $data['id'],
            (string) $data['slug'],
            (string) $data['name'],
            (bool) $data['is_system'],
        );
    }

    public function save(Role $role): void
    {
        $now = date('Y-m-d H:i:s');
        $exists = Db::table(self::TABLE)->where('id', $role->id())->exists();
        if ($exists) {
            Db::table(self::TABLE)->where('id', $role->id())->update([
                'slug' => $role->slug(),
                'name' => $role->name(),
                'is_system' => $role->isSystem(),
                'updated_at' => $now,
            ]);
        } else {
            Db::table(self::TABLE)->insert([
                'id' => $role->id(),
                'slug' => $role->slug(),
                'name' => $role->name(),
                'is_system' => $role->isSystem(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function deleteById(string $id): void
    {
        Db::table(self::TABLE)->where('id', $id)->delete();
    }
}
