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

final class InMemoryTechnologyRepository implements TechnologyRepositoryInterface
{
    /** @var array<string, Technology> */
    private array $items = [];

    public function findById(TechnologyId $id): ?Technology
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findBySlug(Slug $slug): ?Technology
    {
        foreach ($this->items as $technology) {
            if ($technology->slug()->value() === $slug->value()) {
                return $technology;
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_values($this->items);
    }

    public function findByIds(array $ids): array
    {
        return array_values(array_filter($this->items, static fn (Technology $t): bool => in_array($t->id()->value(), $ids, true)));
    }
}
