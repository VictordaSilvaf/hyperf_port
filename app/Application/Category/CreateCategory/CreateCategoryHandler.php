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

namespace App\Application\Category\CreateCategory;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Shared\ValueObject\Slug;
use InvalidArgumentException;

final class CreateCategoryHandler
{
    public function __construct(private readonly CategoryRepositoryInterface $categories)
    {
    }

    /** @return array{data: array{id: string, name: string, slug: string}} */
    public function handle(CreateCategoryCommand $command): array
    {
        $name = trim($command->name);
        if ($name === '') {
            throw new InvalidArgumentException('Category name cannot be empty.');
        }

        $slug = Slug::fromString($command->slug !== null && trim($command->slug) !== '' ? $command->slug : $name);
        $existing = $this->categories->findBySlug($slug);
        if ($existing !== null) {
            return ['data' => $this->toArray($existing)];
        }

        $category = Category::create($name, $slug);
        $this->categories->save($category);

        return ['data' => $this->toArray($category)];
    }

    /** @return array{id: string, name: string, slug: string} */
    private function toArray(Category $category): array
    {
        return [
            'id' => $category->id()->value(),
            'name' => $category->name(),
            'slug' => $category->slug()->value(),
        ];
    }
}
