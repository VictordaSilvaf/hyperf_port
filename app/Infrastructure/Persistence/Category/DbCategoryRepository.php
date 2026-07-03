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

namespace App\Infrastructure\Persistence\Category;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Shared\ValueObject\Slug;
use Hyperf\DbConnection\Db;

final class DbCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?Category
    {
        $row = Db::table('categories')->where('id', $id->value())->first();

        return $row === null ? null : $this->toDomain((array) $row);
    }

    public function findBySlug(Slug $slug): ?Category
    {
        $row = Db::table('categories')->where('slug', $slug->value())->first();

        return $row === null ? null : $this->toDomain((array) $row);
    }

    public function all(): array
    {
        return array_map(fn ($row) => $this->toDomain((array) $row), Db::table('categories')->orderBy('name')->get()->all());
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map(
            fn ($row) => $this->toDomain((array) $row),
            Db::table('categories')->whereIn('id', $ids)->get()->all()
        );
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Category
    {
        return Category::restore(
            CategoryId::fromString((string) $row['id']),
            (string) $row['name'],
            Slug::fromString((string) $row['slug']),
        );
    }
}
