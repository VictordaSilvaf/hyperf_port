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

namespace App\Application\Category\ListCategories;

use App\Domain\Category\Repository\CategoryRepositoryInterface;

final class ListCategoriesHandler
{
    public function __construct(private readonly CategoryRepositoryInterface $categories)
    {
    }

    public function handle(): array
    {
        return array_map(static fn ($c): array => [
            'id' => $c->id()->value(),
            'name' => $c->name(),
            'slug' => $c->slug()->value(),
        ], $this->categories->all());
    }
}
