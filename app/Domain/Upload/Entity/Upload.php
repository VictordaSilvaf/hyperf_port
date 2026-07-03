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

namespace App\Domain\Upload\Entity;

use App\Domain\Upload\ValueObject\UploadId;
use InvalidArgumentException;

final class Upload
{
    private function __construct(
        private readonly UploadId $id,
        private readonly string $path,
        private readonly ?string $url,
        private readonly ?string $mimeType,
        private readonly int $size,
        private readonly ?string $originalName,
    ) {
    }

    public static function create(
        string $path,
        ?string $url,
        ?string $mimeType,
        int $size,
        ?string $originalName,
    ): self {
        if (trim($path) === '') {
            throw new InvalidArgumentException('Upload path cannot be empty.');
        }

        return new self(
            UploadId::generate(),
            $path,
            $url,
            $mimeType,
            max(0, $size),
            $originalName,
        );
    }

    public static function restore(
        UploadId $id,
        string $path,
        ?string $url,
        ?string $mimeType,
        int $size,
        ?string $originalName,
    ): self {
        return new self($id, $path, $url, $mimeType, $size, $originalName);
    }

    public function id(): UploadId
    {
        return $this->id;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function mimeType(): ?string
    {
        return $this->mimeType;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function originalName(): ?string
    {
        return $this->originalName;
    }
}
