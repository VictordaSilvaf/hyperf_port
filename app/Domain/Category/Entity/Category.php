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

namespace App\Domain\Category\Entity;

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Shared\ValueObject\Slug;
use InvalidArgumentException;

final class Category
{
    private function __construct(
        private readonly CategoryId $id,
        private readonly string $name,
        private readonly Slug $slug,
    ) {
    }

    public static function create(string $name, Slug $slug): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Category name cannot be empty.');
        }

        return new self(CategoryId::generate(), $trimmed, $slug);
    }

    public static function restore(CategoryId $id, string $name, Slug $slug): self
    {
        return new self($id, $name, $slug);
    }

    public function id(): CategoryId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): Slug
    {
        return $this->slug;
    }
}
