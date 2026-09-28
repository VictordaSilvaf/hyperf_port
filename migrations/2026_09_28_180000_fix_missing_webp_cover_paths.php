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

/*
 * Cover/thumbnail were sometimes saved as full-size .webp that never landed in R2.
 * Point them back to the optimized .jpg/.png master when the path ends with .webp
 * and is not already a `_thumb.webp`.
 */
return new class extends Migration {
    public function up(): void
    {
        foreach (['cover_path', 'thumbnail_path'] as $column) {
            $rows = Db::table('projects')
                ->whereNotNull($column)
                ->where($column, 'like', '%.webp')
                ->where($column, 'not like', '%_thumb.webp')
                ->get(['id', $column]);

            foreach ($rows as $row) {
                $current = (string) $row->{$column};
                $fixed = preg_replace('/\.webp$/i', '.jpg', $current);
                if (! is_string($fixed) || $fixed === $current) {
                    continue;
                }
                Db::table('projects')->where('id', $row->id)->update([$column => $fixed]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible: original .webp paths may never have existed in object storage.
    }
};
