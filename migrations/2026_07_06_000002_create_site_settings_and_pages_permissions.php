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
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    private const SITE_SETTINGS_ID = '00000000-0000-4000-8000-000000000001';

    private const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    private const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    private const PERM_PAGES_VIEW = 'b0000040-0000-4000-8000-000000000001';

    private const PERM_PAGES_CREATE = 'b0000041-0000-4000-8000-000000000001';

    private const PERM_PAGES_UPDATE = 'b0000042-0000-4000-8000-000000000001';

    private const PERM_PAGES_DELETE = 'b0000043-0000-4000-8000-000000000001';

    private const PERM_PAGES_PUBLISH = 'b0000044-0000-4000-8000-000000000001';

    private const PERM_SITE_UPDATE = 'b0000045-0000-4000-8000-000000000001';

    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->jsonb('nav')->nullable();
            $table->jsonb('footer')->nullable();
            $table->jsonb('social')->nullable();
            $table->jsonb('branding')->nullable();
            $table->jsonb('seo')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $now = date('Y-m-d H:i:s');
        Db::table('site_settings')->insertOrIgnore([
            'id' => self::SITE_SETTINGS_ID,
            'nav' => json_encode([]),
            'footer' => json_encode([]),
            'social' => json_encode([]),
            'branding' => json_encode([]),
            'seo' => json_encode(['site_name' => 'Victor Dev', 'locale' => 'pt_BR']),
            'updated_at' => $now,
        ]);

        $permissions = [
            ['id' => self::PERM_PAGES_VIEW, 'slug' => 'pages.view', 'description' => 'Listar páginas'],
            ['id' => self::PERM_PAGES_CREATE, 'slug' => 'pages.create', 'description' => 'Criar páginas'],
            ['id' => self::PERM_PAGES_UPDATE, 'slug' => 'pages.update', 'description' => 'Editar e reordenar páginas'],
            ['id' => self::PERM_PAGES_DELETE, 'slug' => 'pages.delete', 'description' => 'Eliminar páginas'],
            ['id' => self::PERM_PAGES_PUBLISH, 'slug' => 'pages.publish', 'description' => 'Publicar e arquivar páginas'],
            ['id' => self::PERM_SITE_UPDATE, 'slug' => 'site.update', 'description' => 'Editar configurações do site'],
        ];

        foreach ($permissions as $p) {
            Db::table('permissions')->insertOrIgnore([
                'id' => $p['id'],
                'slug' => $p['slug'],
                'description' => $p['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminPerms = [
            self::PERM_PAGES_VIEW,
            self::PERM_PAGES_CREATE,
            self::PERM_PAGES_UPDATE,
            self::PERM_PAGES_DELETE,
            self::PERM_PAGES_PUBLISH,
            self::PERM_SITE_UPDATE,
        ];

        foreach ($adminPerms as $permId) {
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_ADMIN,
                'permission_id' => $permId,
            ]);
        }

        $managerPerms = [
            self::PERM_PAGES_VIEW,
            self::PERM_PAGES_CREATE,
            self::PERM_PAGES_UPDATE,
            self::PERM_PAGES_PUBLISH,
            self::PERM_SITE_UPDATE,
        ];

        foreach ($managerPerms as $permId) {
            Db::table('role_permission')->insertOrIgnore([
                'role_id' => self::ROLE_MANAGER,
                'permission_id' => $permId,
            ]);
        }
    }

    public function down(): void
    {
        $permIds = [
            self::PERM_PAGES_VIEW,
            self::PERM_PAGES_CREATE,
            self::PERM_PAGES_UPDATE,
            self::PERM_PAGES_DELETE,
            self::PERM_PAGES_PUBLISH,
            self::PERM_SITE_UPDATE,
        ];

        Db::table('role_permission')->whereIn('permission_id', $permIds)->delete();
        Db::table('permissions')->whereIn('id', $permIds)->delete();
        Schema::dropIfExists('site_settings');
    }
};
