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
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

/*
 * Seed IDs used invalid hex (`p`, `t`, `g`) and fail UUID v4 validation → HTTP 500 on admin GET.
 * Remap to valid UUID v4 strings; keep slugs/content.
 *
 * Inserts the new row with a temporary slug to avoid unique(slug) collisions, then restores it.
 */
return new class extends Migration {
    public function up(): void
    {
        $this->remapPrimary('technologies', [
            't1000001-0000-4000-8000-000000000001' => 'b1000001-0000-4000-8000-000000000001',
            't1000002-0000-4000-8000-000000000001' => 'b1000002-0000-4000-8000-000000000001',
            't1000003-0000-4000-8000-000000000001' => 'b1000003-0000-4000-8000-000000000001',
            't1000004-0000-4000-8000-000000000001' => 'b1000004-0000-4000-8000-000000000001',
        ], [
            ['project_technology', 'technology_id'],
        ]);

        $this->remapPrimary('tags', [
            'g1000001-0000-4000-8000-000000000001' => 'd1000001-0000-4000-8000-000000000001',
            'g1000002-0000-4000-8000-000000000001' => 'd1000002-0000-4000-8000-000000000001',
        ], [
            ['project_tag', 'tag_id'],
        ]);

        $this->remapPrimary('projects', [
            'p1000001-0000-4000-8000-000000000001' => 'a1000001-0000-4000-8000-000000000001',
            'p1000002-0000-4000-8000-000000000001' => 'a1000002-0000-4000-8000-000000000001',
            'p1000003-0000-4000-8000-000000000001' => 'a1000003-0000-4000-8000-000000000001',
        ], [
            ['category_project', 'project_id'],
            ['project_technology', 'project_id'],
            ['project_tag', 'project_id'],
            ['project_images', 'project_id'],
            ['posts', 'project_id'],
        ]);
    }

    public function down(): void
    {
        $this->remapPrimary('projects', [
            'a1000001-0000-4000-8000-000000000001' => 'p1000001-0000-4000-8000-000000000001',
            'a1000002-0000-4000-8000-000000000001' => 'p1000002-0000-4000-8000-000000000001',
            'a1000003-0000-4000-8000-000000000001' => 'p1000003-0000-4000-8000-000000000001',
        ], [
            ['category_project', 'project_id'],
            ['project_technology', 'project_id'],
            ['project_tag', 'project_id'],
            ['project_images', 'project_id'],
            ['posts', 'project_id'],
        ]);

        $this->remapPrimary('tags', [
            'd1000001-0000-4000-8000-000000000001' => 'g1000001-0000-4000-8000-000000000001',
            'd1000002-0000-4000-8000-000000000001' => 'g1000002-0000-4000-8000-000000000001',
        ], [
            ['project_tag', 'tag_id'],
        ]);

        $this->remapPrimary('technologies', [
            'b1000001-0000-4000-8000-000000000001' => 't1000001-0000-4000-8000-000000000001',
            'b1000002-0000-4000-8000-000000000001' => 't1000002-0000-4000-8000-000000000001',
            'b1000003-0000-4000-8000-000000000001' => 't1000003-0000-4000-8000-000000000001',
            'b1000004-0000-4000-8000-000000000001' => 't1000004-0000-4000-8000-000000000001',
        ], [
            ['project_technology', 'technology_id'],
        ]);
    }

    /**
     * @param array<string, string> $map oldId => newId
     * @param list<array{0: string, 1: string}> $foreigns [table, column]
     */
    private function remapPrimary(string $table, array $map, array $foreigns): void
    {
        foreach ($map as $oldId => $newId) {
            $row = Db::table($table)->where('id', $oldId)->first();
            if ($row === null) {
                continue;
            }

            if (! Db::table($table)->where('id', $newId)->exists()) {
                $data = (array) $row;
                $data['id'] = $newId;
                $originalSlug = null;
                if (array_key_exists('slug', $data) && is_string($data['slug']) && $data['slug'] !== '') {
                    $originalSlug = $data['slug'];
                    // Avoid unique(slug) while old + new rows coexist.
                    $data['slug'] = $originalSlug . '__remap_' . substr(md5($oldId), 0, 8);
                }
                Db::table($table)->insert($data);
                if ($originalSlug !== null) {
                    // Will restore after deleting the old row.
                    $data['_restore_slug'] = $originalSlug;
                }
            }

            foreach ($foreigns as [$fkTable, $fkColumn]) {
                if (! Schema::hasTable($fkTable)) {
                    continue;
                }
                Db::table($fkTable)->where($fkColumn, $oldId)->update([$fkColumn => $newId]);
            }

            Db::table($table)->where('id', $oldId)->delete();

            $newRow = Db::table($table)->where('id', $newId)->first();
            if ($newRow !== null && isset($newRow->slug) && is_string($newRow->slug) && str_contains($newRow->slug, '__remap_')) {
                $restored = preg_replace('/__remap_[a-f0-9]{8}$/', '', $newRow->slug) ?? $newRow->slug;
                Db::table($table)->where('id', $newId)->update(['slug' => $restored]);
            }
        }
    }
};
