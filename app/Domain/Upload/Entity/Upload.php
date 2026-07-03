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
use App\Domain\Upload\ValueObject\UploadProcessingStatus;
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
        private readonly UploadProcessingStatus $processingStatus,
        private readonly ?string $webpPath,
        private readonly ?string $webpUrl,
        private readonly ?string $thumbnailPath,
        private readonly ?string $thumbnailUrl,
        private readonly ?int $width,
        private readonly ?int $height,
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

        $status = self::isImageMime($mimeType)
            ? UploadProcessingStatus::Pending
            : UploadProcessingStatus::Skipped;

        return new self(
            UploadId::generate(),
            $path,
            $url,
            $mimeType,
            max(0, $size),
            $originalName,
            $status,
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }

    public static function restore(
        UploadId $id,
        string $path,
        ?string $url,
        ?string $mimeType,
        int $size,
        ?string $originalName,
        UploadProcessingStatus $processingStatus = UploadProcessingStatus::Skipped,
        ?string $webpPath = null,
        ?string $webpUrl = null,
        ?string $thumbnailPath = null,
        ?string $thumbnailUrl = null,
        ?int $width = null,
        ?int $height = null,
    ): self {
        return new self(
            $id,
            $path,
            $url,
            $mimeType,
            $size,
            $originalName,
            $processingStatus,
            $webpPath,
            $webpUrl,
            $thumbnailPath,
            $thumbnailUrl,
            $width,
            $height,
        );
    }

    public function markProcessing(): self
    {
        return $this->with(['processing_status' => UploadProcessingStatus::Processing]);
    }

    public function markSkipped(): self
    {
        return $this->with(['processing_status' => UploadProcessingStatus::Skipped]);
    }

    public function markFailed(): self
    {
        return $this->with(['processing_status' => UploadProcessingStatus::Failed]);
    }

    /**
     * @param array{
     *   path?: string,
     *   url?: null|string,
     *   size?: int,
     *   processing_status?: UploadProcessingStatus,
     *   webp_path?: null|string,
     *   webp_url?: null|string,
     *   thumbnail_path?: null|string,
     *   thumbnail_url?: null|string,
     *   width?: null|int,
     *   height?: null|int,
     * } $changes
     */
    public function with(array $changes): self
    {
        return new self(
            $this->id,
            $changes['path'] ?? $this->path,
            array_key_exists('url', $changes) ? $changes['url'] : $this->url,
            $this->mimeType,
            $changes['size'] ?? $this->size,
            $this->originalName,
            $changes['processing_status'] ?? $this->processingStatus,
            array_key_exists('webp_path', $changes) ? $changes['webp_path'] : $this->webpPath,
            array_key_exists('webp_url', $changes) ? $changes['webp_url'] : $this->webpUrl,
            array_key_exists('thumbnail_path', $changes) ? $changes['thumbnail_path'] : $this->thumbnailPath,
            array_key_exists('thumbnail_url', $changes) ? $changes['thumbnail_url'] : $this->thumbnailUrl,
            array_key_exists('width', $changes) ? $changes['width'] : $this->width,
            array_key_exists('height', $changes) ? $changes['height'] : $this->height,
        );
    }

    public function withProcessedVariants(
        string $optimizedPath,
        ?string $optimizedUrl,
        int $optimizedSize,
        string $webpPath,
        ?string $webpUrl,
        string $thumbnailPath,
        ?string $thumbnailUrl,
        int $width,
        int $height,
    ): self {
        return new self(
            $this->id,
            $optimizedPath,
            $optimizedUrl,
            $this->mimeType,
            $optimizedSize,
            $this->originalName,
            UploadProcessingStatus::Completed,
            $webpPath,
            $webpUrl,
            $thumbnailPath,
            $thumbnailUrl,
            $width,
            $height,
        );
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

    public function processingStatus(): UploadProcessingStatus
    {
        return $this->processingStatus;
    }

    public function webpPath(): ?string
    {
        return $this->webpPath;
    }

    public function webpUrl(): ?string
    {
        return $this->webpUrl;
    }

    public function thumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function width(): ?int
    {
        return $this->width;
    }

    public function height(): ?int
    {
        return $this->height;
    }

    public function isImage(): bool
    {
        return self::isImageMime($this->mimeType);
    }

    public function displayUrl(): ?string
    {
        if ($this->processingStatus === UploadProcessingStatus::Completed && $this->webpUrl !== null) {
            return $this->webpUrl;
        }

        return $this->url;
    }

    public function displayThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl ?? $this->displayUrl();
    }

    private static function isImageMime(?string $mimeType): bool
    {
        return $mimeType !== null && str_starts_with(strtolower($mimeType), 'image/');
    }
}
