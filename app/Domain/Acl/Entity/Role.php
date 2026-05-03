<?php

declare(strict_types=1);

namespace App\Domain\Acl\Entity;

use InvalidArgumentException;

final class Role
{
    private function __construct(
        private readonly string $id,
        private readonly string $slug,
        private readonly string $name,
        private readonly bool $isSystem,
    ) {
    }

    public static function create(string $name, string $slug): self
    {
        self::assertSlug($slug);
        $trimmedName = trim($name);
        if ($trimmedName === '') {
            throw new InvalidArgumentException('Role name cannot be empty.');
        }

        return new self(self::newUuid(), $slug, $trimmedName, false);
    }

    public static function restore(string $id, string $slug, string $name, bool $isSystem): self
    {
        self::assertSlug($slug);

        return new self($id, $slug, trim($name), $isSystem);
    }

    public function rename(string $name): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Role name cannot be empty.');
        }

        return new self($this->id, $this->slug, $trimmed, $this->isSystem);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    private static function assertSlug(string $slug): void
    {
        if (preg_match('/^[a-z0-9_-]{1,64}$/', $slug) !== 1) {
            throw new InvalidArgumentException('Invalid role slug: use lowercase letters, numbers, hyphen or underscore (max 64).');
        }
    }

    private static function newUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
