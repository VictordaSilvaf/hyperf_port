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

namespace App\Domain\Project\Entity;

use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;
use App\Domain\Upload\ValueObject\UploadId;

final class ProjectImage
{
    private function __construct(
        private readonly ProjectImageId $id,
        private readonly ProjectId $projectId,
        private readonly UploadId $uploadId,
        private readonly ?string $caption,
        private readonly int $sortOrder,
    ) {
    }

    public static function create(
        ProjectId $projectId,
        UploadId $uploadId,
        ?string $caption,
        int $sortOrder,
    ): self {
        return new self(ProjectImageId::generate(), $projectId, $uploadId, self::normalizeCaption($caption), $sortOrder);
    }

    public static function restore(
        ProjectImageId $id,
        ProjectId $projectId,
        UploadId $uploadId,
        ?string $caption,
        int $sortOrder,
    ): self {
        return new self($id, $projectId, $uploadId, $caption, $sortOrder);
    }

    public function withCaption(?string $caption): self
    {
        return new self($this->id, $this->projectId, $this->uploadId, self::normalizeCaption($caption), $this->sortOrder);
    }

    public function withSortOrder(int $sortOrder): self
    {
        return new self($this->id, $this->projectId, $this->uploadId, $this->caption, $sortOrder);
    }

    public function id(): ProjectImageId
    {
        return $this->id;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function uploadId(): UploadId
    {
        return $this->uploadId;
    }

    public function caption(): ?string
    {
        return $this->caption;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    private static function normalizeCaption(?string $caption): ?string
    {
        if ($caption === null) {
            return null;
        }
        $trimmed = trim($caption);

        return $trimmed === '' ? null : $trimmed;
    }
}
