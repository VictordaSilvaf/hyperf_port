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

namespace App\Domain\Category\Repository;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Shared\ValueObject\Slug;

interface CategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?Category;

    public function findBySlug(Slug $slug): ?Category;

    /** @return list<Category> */
    public function all(): array;

    /** @param list<string> $ids */
    public function findByIds(array $ids): array;
}
