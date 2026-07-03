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

namespace App\Infrastructure\Persistence\Tag;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepositoryInterface;
use App\Domain\Tag\ValueObject\TagId;
use Hyperf\DbConnection\Db;

final class DbTagRepository implements TagRepositoryInterface
{
    public function findById(TagId $id): ?Tag
    {
        $row = Db::table('tags')->where('id', $id->value())->first();

        return $row === null ? null : $this->toDomain((array) $row);
    }

    public function findBySlug(Slug $slug): ?Tag
    {
        $row = Db::table('tags')->where('slug', $slug->value())->first();

        return $row === null ? null : $this->toDomain((array) $row);
    }

    public function all(): array
    {
        return array_map(fn ($row) => $this->toDomain((array) $row), Db::table('tags')->orderBy('name')->get()->all());
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map(
            fn ($row) => $this->toDomain((array) $row),
            Db::table('tags')->whereIn('id', $ids)->get()->all()
        );
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Tag
    {
        return Tag::restore(
            TagId::fromString((string) $row['id']),
            (string) $row['name'],
            Slug::fromString((string) $row['slug']),
        );
    }
}
