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

final class InMemoryCategoryRepository implements CategoryRepositoryInterface
{
    /** @var array<string, Category> */
    private array $items = [];

    public function findById(CategoryId $id): ?Category
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findBySlug(Slug $slug): ?Category
    {
        foreach ($this->items as $category) {
            if ($category->slug()->value() === $slug->value()) {
                return $category;
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
        return array_values(array_filter($this->items, static fn (Category $c): bool => in_array($c->id()->value(), $ids, true)));
    }
}
