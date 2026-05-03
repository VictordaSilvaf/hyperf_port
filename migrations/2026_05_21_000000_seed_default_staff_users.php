<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    /** Deve coincidir com `2026_05_20_000000_create_rbac_tables.php`. */
    private const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    private const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    private const USER_ADMIN = 'c0000001-0000-4000-8000-000000000001';

    private const USER_MANAGER = 'c0000002-0000-4000-8000-000000000001';

    /** Palavra-passe inicial (documentada no README); trocar em produção. */
    private const DEFAULT_PLAIN_PASSWORD = 'VictorDev123!';

    public function up(): void
    {
        $hash = password_hash(self::DEFAULT_PLAIN_PASSWORD, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'id' => self::USER_ADMIN,
                'name' => 'Administrador',
                'email' => 'admin@victordev.com',
                'role_id' => self::ROLE_ADMIN,
            ],
            [
                'id' => self::USER_MANAGER,
                'name' => 'Gestor',
                'email' => 'manager@victordev.com',
                'role_id' => self::ROLE_MANAGER,
            ],
        ];

        foreach ($rows as $row) {
            if (Db::table('users')->where('email', $row['email'])->exists()) {
                continue;
            }

            Db::table('users')->insert([
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $hash,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            Db::table('user_role')->insertOrIgnore([
                'user_id' => $row['id'],
                'role_id' => $row['role_id'],
            ]);
        }
    }

    public function down(): void
    {
        $emails = ['admin@victordev.com', 'manager@victordev.com'];
        $ids = Db::table('users')->whereIn('email', $emails)->pluck('id');
        foreach ($ids as $id) {
            Db::table('user_role')->where('user_id', (string) $id)->delete();
        }
        Db::table('users')->whereIn('email', $emails)->delete();
    }
};
