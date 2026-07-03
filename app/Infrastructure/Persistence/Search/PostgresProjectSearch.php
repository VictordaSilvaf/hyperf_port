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

namespace App\Infrastructure\Persistence\Search;

use Hyperf\Database\Query\Builder;

final class PostgresProjectSearch
{
    public static function apply(Builder $builder, string $query, string $alias = 'p'): void
    {
        $term = trim($query);
        if ($term === '') {
            return;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';
        $builder->whereRaw(
            "(
                {$alias}.search_vector @@ plainto_tsquery('portuguese', ?)
                OR {$alias}.title ILIKE ?
                OR {$alias}.slug ILIKE ?
                OR {$alias}.title % ?
                OR {$alias}.slug % ?
            )",
            [$term, $like, $like, $term, $term],
        );
    }

    public static function applyRelevanceOrder(Builder $builder, string $query, string $alias = 'p'): void
    {
        $term = trim($query);
        if ($term === '') {
            return;
        }

        $builder->orderByRaw(
            "GREATEST(
                ts_rank({$alias}.search_vector, plainto_tsquery('portuguese', ?)),
                similarity({$alias}.title, ?),
                similarity({$alias}.slug, ?)
            ) DESC",
            [$term, $term, $term],
        );
    }
}
