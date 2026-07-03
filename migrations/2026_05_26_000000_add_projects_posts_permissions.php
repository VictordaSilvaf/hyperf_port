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

    private const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    private const PERM_PROJECTS_VIEW = 'b0000020-0000-4000-8000-000000000001';

    private const PERM_PROJECTS_CREATE = 'b0000021-0000-4000-8000-000000000001';

    private const PERM_PROJECTS_UPDATE = 'b0000022-0000-4000-8000-000000000001';

    private const PERM_PROJECTS_DELETE = 'b0000023-0000-4000-8000-000000000001';

    private const PERM_PROJECTS_PUBLISH = 'b0000024-0000-4000-8000-000000000001';

    private const PERM_POSTS_VIEW = 'b0000030-0000-4000-8000-000000000001';

    private const PERM_POSTS_CREATE = 'b0000031-0000-4000-8000-000000000001';

    private const PERM_POSTS_UPDATE = 'b0000032-0000-4000-8000-000000000001';

    private const PERM_POSTS_DELETE = 'b0000033-0000-4000-8000-000000000001';

    private const PERM_POSTS_PUBLISH = 'b0000034-0000-4000-8000-000000000001';

    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [
            ['id' => self::PERM_PROJECTS_VIEW, 'slug' => 'projects.view', 'description' => 'Listar projetos'],
            ['id' => self::PERM_PROJECTS_CREATE, 'slug' => 'projects.create', 'description' => 'Criar projetos'],
            ['id' => self::PERM_PROJECTS_UPDATE, 'slug' => 'projects.update', 'description' => 'Editar e reordenar projetos'],
            ['id' => self::PERM_PROJECTS_DELETE, 'slug' => 'projects.delete', 'description' => 'Eliminar projetos'],
            ['id' => self::PERM_PROJECTS_PUBLISH, 'slug' => 'projects.publish', 'description' => 'Publicar e arquivar projetos'],
            ['id' => self::PERM_POSTS_VIEW, 'slug' => 'posts.view', 'description' => 'Listar posts'],
            ['id' => self::PERM_POSTS_CREATE, 'slug' => 'posts.create', 'description' => 'Criar posts'],
            ['id' => self::PERM_POSTS_UPDATE, 'slug' => 'posts.update', 'description' => 'Editar posts'],
            ['id' => self::PERM_POSTS_DELETE, 'slug' => 'posts.delete', 'description' => 'Eliminar posts'],
            ['id' => self::PERM_POSTS_PUBLISH, 'slug' => 'posts.publish', 'description' => 'Publicar posts'],
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

        $permIds = array_column($rows, 'id');
        foreach ($permIds as $pid) {
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_ADMIN,
                'permission_id' => $pid,
            ]);
        }

        $managerPerms = [
            self::PERM_PROJECTS_VIEW,
            self::PERM_PROJECTS_CREATE,
            self::PERM_PROJECTS_UPDATE,
            self::PERM_POSTS_VIEW,
            self::PERM_POSTS_CREATE,
            self::PERM_POSTS_UPDATE,
        ];
        foreach ($managerPerms as $pid) {
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_MANAGER,
                'permission_id' => $pid,
            ]);
        }
    }

    public function down(): void
    {
        $ids = [
            self::PERM_PROJECTS_VIEW,
            self::PERM_PROJECTS_CREATE,
            self::PERM_PROJECTS_UPDATE,
            self::PERM_PROJECTS_DELETE,
            self::PERM_PROJECTS_PUBLISH,
            self::PERM_POSTS_VIEW,
            self::PERM_POSTS_CREATE,
            self::PERM_POSTS_UPDATE,
            self::PERM_POSTS_DELETE,
            self::PERM_POSTS_PUBLISH,
        ];
        Db::table('role_permission')->whereIn('permission_id', $ids)->delete();
        Db::table('permissions')->whereIn('id', $ids)->delete();
    }
};
