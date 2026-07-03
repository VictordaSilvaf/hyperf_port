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
use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    private const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    private const PERM_UPLOADS_CREATE = 'b0000040-0000-4000-8000-000000000001';

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        Db::table('permissions')->insertOrIgnore([
            'id' => self::PERM_UPLOADS_CREATE,
            'slug' => 'uploads.create',
            'description' => 'Upload de ficheiros',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        Db::table('role_permission')->insertOrIgnore([
            'role_id' => self::ROLE_ADMIN,
            'permission_id' => self::PERM_UPLOADS_CREATE,
        ]);
    }

    public function down(): void
    {
        Db::table('role_permission')->where('permission_id', self::PERM_UPLOADS_CREATE)->delete();
        Db::table('permissions')->where('id', self::PERM_UPLOADS_CREATE)->delete();
    }
};
