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

namespace App\Domain\Technology\Entity;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Technology\ValueObject\TechnologyId;
use InvalidArgumentException;

final class Technology
{
    private function __construct(
        private readonly TechnologyId $id,
        private readonly string $name,
        private readonly Slug $slug,
    ) {
    }

    public static function create(string $name, Slug $slug): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Technology name cannot be empty.');
        }

        return new self(TechnologyId::generate(), $trimmed, $slug);
    }

    public static function restore(TechnologyId $id, string $name, Slug $slug): self
    {
        return new self($id, $name, $slug);
    }

    public function id(): TechnologyId
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
