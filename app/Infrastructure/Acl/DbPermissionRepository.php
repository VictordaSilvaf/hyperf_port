<?php

declare(strict_types=1);

namespace App\Infrastructure\Acl;

use App\Domain\Acl\Repository\PermissionRepositoryInterface;
use Hyperf\DbConnection\Db;

final class DbPermissionRepository implements PermissionRepositoryInterface
{
    private const TABLE = 'permissions';

    public function all(): array
    {
        $rows = Db::table(self::TABLE)->orderBy('slug')->get();
        $out = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $out[] = [
                'id' => (string) $data['id'],
                'slug' => (string) $data['slug'],
                'description' => isset($data['description']) ? (string) $data['description'] : null,
            ];
        }

        return $out;
    }

    public function findIdBySlug(string $slug): ?string
    {
        $id = Db::table(self::TABLE)->where('slug', $slug)->value('id');

        return $id === null ? null : (string) $id;
    }
}
