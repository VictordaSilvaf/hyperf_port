<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    private const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    private const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    private const PERM_USERS_CREATE = 'b0000010-0000-4000-8000-000000000001';

    private const PERM_USERS_UPDATE = 'b0000011-0000-4000-8000-000000000001';

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            [
                'id' => self::PERM_USERS_CREATE,
                'slug' => 'users.create',
                'description' => 'Criar utilizadores (backoffice)',
            ],
            [
                'id' => self::PERM_USERS_UPDATE,
                'slug' => 'users.update',
                'description' => 'Editar nome e e-mail de utilizadores',
            ],
        ];
        foreach ($rows as $p) {
            Db::table('permissions')->insertOrIgnore([
                'id' => $p['id'],
                'slug' => $p['slug'],
                'description' => $p['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ([self::PERM_USERS_CREATE, self::PERM_USERS_UPDATE] as $pid) {
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_ADMIN,
                'permission_id' => $pid,
            ]);
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_MANAGER,
                'permission_id' => $pid,
            ]);
        }
    }

    public function down(): void
    {
        $ids = [self::PERM_USERS_CREATE, self::PERM_USERS_UPDATE];
        Db::table('role_permission')->whereIn('permission_id', $ids)->delete();
        Db::table('permissions')->whereIn('id', $ids)->delete();
    }
};
