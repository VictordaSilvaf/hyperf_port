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

namespace App\Infrastructure\Persistence\Technology;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Technology\Entity\Technology;
use App\Domain\Technology\Repository\TechnologyRepositoryInterface;
use App\Domain\Technology\ValueObject\TechnologyId;
use Hyperf\DbConnection\Db;

final class DbTechnologyRepository implements TechnologyRepositoryInterface
{
    public function findById(TechnologyId $id): ?Technology
    {
        $row = Db::table('technologies')->where('id', $id->value())->first();

        return $row === null ? null : $this->toDomain((array) $row);
    }

    public function findBySlug(Slug $slug): ?Technology
    {
        $row = Db::table('technologies')->where('slug', $slug->value())->first();

        return $row === null ? null : $this->toDomain((array) $row);
    }

    public function all(): array
    {
        return array_map(fn ($row) => $this->toDomain((array) $row), Db::table('technologies')->orderBy('name')->get()->all());
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map(
            fn ($row) => $this->toDomain((array) $row),
            Db::table('technologies')->whereIn('id', $ids)->get()->all()
        );
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Technology
    {
        return Technology::restore(
            TechnologyId::fromString((string) $row['id']),
            (string) $row['name'],
            Slug::fromString((string) $row['slug']),
        );
    }
}
