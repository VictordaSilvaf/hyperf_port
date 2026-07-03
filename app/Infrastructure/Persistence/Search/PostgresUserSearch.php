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

final class PostgresUserSearch
{
    public static function apply(Builder $builder, string $query): void
    {
        $term = trim($query);
        if ($term === '') {
            return;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';
        $builder->whereRaw(
            '(
                name % ?
                OR email % ?
                OR name ILIKE ?
                OR email ILIKE ?
            )',
            [$term, $term, $like, $like],
        );
    }

    public static function applyRelevanceOrder(Builder $builder, string $query): void
    {
        $term = trim($query);
        if ($term === '') {
            return;
        }

        $builder->orderByRaw(
            'GREATEST(similarity(name, ?), similarity(email, ?)) DESC',
            [$term, $term],
        );
    }
}
