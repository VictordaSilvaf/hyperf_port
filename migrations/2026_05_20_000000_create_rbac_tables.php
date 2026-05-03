<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    private const ROLE_ADMIN = 'a0000001-0000-4000-8000-000000000001';

    private const ROLE_MANAGER = 'a0000002-0000-4000-8000-000000000001';

    private const ROLE_USER = 'a0000003-0000-4000-8000-000000000001';

    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('slug', 64)->unique();
            $table->string('name', 120);
            $table->boolean('is_system')->default(false);
            $table->datetimes();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('slug', 128)->unique();
            $table->string('description', 255)->nullable();
            $table->datetimes();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->string('role_id', 36);
            $table->string('permission_id', 36);
            $table->primary(['role_id', 'permission_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->string('user_id', 36);
            $table->string('role_id', 36);
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        $now = date('Y-m-d H:i:s');

        $perms = [
            ['id' => 'b0000001-0000-4000-8000-000000000001', 'slug' => 'users.view', 'description' => 'Ver utilizadores'],
            ['id' => 'b0000002-0000-4000-8000-000000000001', 'slug' => 'users.assign_roles', 'description' => 'Atribuir papéis a utilizadores'],
            ['id' => 'b0000003-0000-4000-8000-000000000001', 'slug' => 'roles.view', 'description' => 'Listar papéis'],
            ['id' => 'b0000004-0000-4000-8000-000000000001', 'slug' => 'roles.create', 'description' => 'Criar papéis'],
            ['id' => 'b0000005-0000-4000-8000-000000000001', 'slug' => 'roles.delete', 'description' => 'Eliminar papéis não-sistema'],
            ['id' => 'b0000006-0000-4000-8000-000000000001', 'slug' => 'roles.assign_permissions', 'description' => 'Definir permissões de um papel'],
            ['id' => 'b0000007-0000-4000-8000-000000000001', 'slug' => 'permissions.view', 'description' => 'Listar permissões'],
        ];

        foreach ($perms as $p) {
            Db::table('permissions')->insert([
                'id' => $p['id'],
                'slug' => $p['slug'],
                'description' => $p['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Db::table('roles')->insert([
            ['id' => self::ROLE_ADMIN, 'slug' => 'admin', 'name' => 'Administrador', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => self::ROLE_MANAGER, 'slug' => 'manager', 'name' => 'Gestor', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => self::ROLE_USER, 'slug' => 'user', 'name' => 'Utilizador', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $allPermIds = array_column($perms, 'id');

        foreach ($allPermIds as $pid) {
            Db::table('role_permission')->insert([
                'role_id' => self::ROLE_ADMIN,
                'permission_id' => $pid,
            ]);
        }

        $managerPerms = [
            'b0000001-0000-4000-8000-000000000001',
            'b0000003-0000-4000-8000-000000000001',
            'b0000007-0000-4000-8000-000000000001',
        ];
        foreach ($managerPerms as $pid) {
            Db::table('role_permission')->insert([
                'role_id' => self::ROLE_MANAGER,
                'permission_id' => $pid,
            ]);
        }

        foreach (Db::table('users')->pluck('id') as $userId) {
            Db::table('user_role')->insertOrIgnore([
                'user_id' => (string) $userId,
                'role_id' => self::ROLE_USER,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
