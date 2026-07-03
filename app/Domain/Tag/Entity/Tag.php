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

namespace App\Domain\Tag\Entity;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Tag\ValueObject\TagId;
use InvalidArgumentException;

final class Tag
{
    private function __construct(
        private readonly TagId $id,
        private readonly string $name,
        private readonly Slug $slug,
    ) {
    }

    public static function create(string $name, Slug $slug): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Tag name cannot be empty.');
        }

        return new self(TagId::generate(), $trimmed, $slug);
    }

    public static function restore(TagId $id, string $name, Slug $slug): self
    {
        return new self($id, $name, $slug);
    }

    public function id(): TagId
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
