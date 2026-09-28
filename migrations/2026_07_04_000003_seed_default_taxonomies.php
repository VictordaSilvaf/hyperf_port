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
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');

        $categories = [
            ['id' => 'c1000001-0000-4000-8000-000000000001', 'name' => 'Web', 'slug' => 'web'],
            ['id' => 'c1000002-0000-4000-8000-000000000001', 'name' => 'Mobile', 'slug' => 'mobile'],
            ['id' => 'c1000003-0000-4000-8000-000000000001', 'name' => '3D', 'slug' => '3d'],
        ];

        $technologies = [
            ['id' => 'b1000001-0000-4000-8000-000000000001', 'name' => 'Laravel', 'slug' => 'laravel'],
            ['id' => 'b1000002-0000-4000-8000-000000000001', 'name' => 'React', 'slug' => 'react'],
            ['id' => 'b1000003-0000-4000-8000-000000000001', 'name' => 'Hyperf', 'slug' => 'hyperf'],
            ['id' => 'b1000004-0000-4000-8000-000000000001', 'name' => 'PostgreSQL', 'slug' => 'postgresql'],
        ];

        $tags = [
            ['id' => 'd1000001-0000-4000-8000-000000000001', 'name' => 'Open Source', 'slug' => 'open-source'],
            ['id' => 'd1000002-0000-4000-8000-000000000001', 'name' => 'Portfolio', 'slug' => 'portfolio'],
        ];

        foreach ($categories as $row) {
            Db::table('categories')->insertOrIgnore([...$row, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ($technologies as $row) {
            Db::table('technologies')->insertOrIgnore([...$row, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ($tags as $row) {
            Db::table('tags')->insertOrIgnore([...$row, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Db::table('categories')->whereIn('id', [
            'c1000001-0000-4000-8000-000000000001',
            'c1000002-0000-4000-8000-000000000001',
            'c1000003-0000-4000-8000-000000000001',
        ])->delete();
        Db::table('technologies')->whereIn('id', [
            'b1000001-0000-4000-8000-000000000001',
            'b1000002-0000-4000-8000-000000000001',
            'b1000003-0000-4000-8000-000000000001',
            'b1000004-0000-4000-8000-000000000001',
        ])->delete();
        Db::table('tags')->whereIn('id', [
            'd1000001-0000-4000-8000-000000000001',
            'd1000002-0000-4000-8000-000000000001',
        ])->delete();
    }
};
