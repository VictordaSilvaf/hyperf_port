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

namespace App\Domain\Shared\ValueObject;

use InvalidArgumentException;

final class Slug
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = self::normalize($value);
        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new InvalidArgumentException('Invalid slug.');
        }

        return new self($normalized);
    }

    public static function normalize(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }

    public function value(): string
    {
        return $this->value;
    }
}
