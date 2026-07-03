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

final class InMemoryTagRepository implements TagRepositoryInterface
{
    /** @var array<string, Tag> */
    private array $items = [];

    public function findById(TagId $id): ?Tag
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findBySlug(Slug $slug): ?Tag
    {
        foreach ($this->items as $tag) {
            if ($tag->slug()->value() === $slug->value()) {
                return $tag;
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
        return array_values(array_filter($this->items, static fn (Tag $t): bool => in_array($t->id()->value(), $ids, true)));
    }
}
