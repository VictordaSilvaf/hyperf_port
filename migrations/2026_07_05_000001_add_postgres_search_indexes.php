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
        Db::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        Db::statement("
            ALTER TABLE projects
            ADD COLUMN IF NOT EXISTS search_vector tsvector
            GENERATED ALWAYS AS (
                setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('simple', coalesce(slug, '')), 'A') ||
                setweight(to_tsvector('portuguese', coalesce(description, '')), 'B') ||
                setweight(to_tsvector('portuguese', coalesce(content, '')), 'C')
            ) STORED
        ");

        Db::statement('CREATE INDEX IF NOT EXISTS projects_search_vector_gin_idx ON projects USING GIN (search_vector)');
        Db::statement('CREATE INDEX IF NOT EXISTS projects_title_trgm_idx ON projects USING GIN (title gin_trgm_ops)');
        Db::statement('CREATE INDEX IF NOT EXISTS projects_slug_trgm_idx ON projects USING GIN (slug gin_trgm_ops)');

        Db::statement('CREATE INDEX IF NOT EXISTS users_name_trgm_idx ON users USING GIN (name gin_trgm_ops)');
        Db::statement('CREATE INDEX IF NOT EXISTS users_email_trgm_idx ON users USING GIN (email gin_trgm_ops)');
    }

    public function down(): void
    {
        Db::statement('DROP INDEX IF EXISTS users_email_trgm_idx');
        Db::statement('DROP INDEX IF EXISTS users_name_trgm_idx');
        Db::statement('DROP INDEX IF EXISTS projects_slug_trgm_idx');
        Db::statement('DROP INDEX IF EXISTS projects_title_trgm_idx');
        Db::statement('DROP INDEX IF EXISTS projects_search_vector_gin_idx');
        Db::statement('ALTER TABLE projects DROP COLUMN IF EXISTS search_vector');
    }
};
