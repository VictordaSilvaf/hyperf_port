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
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;
use InvalidArgumentException;

final class Project
{
    private function __construct(
        private readonly ProjectId $id,
        private readonly string $title,
        private readonly ProjectSlug $slug,
        private readonly ?string $description,
        private readonly ProjectStatus $status,
        private readonly int $sortOrder,
        private readonly ?string $imagePath,
        private readonly ?string $ownerId,
    ) {
    }

    public static function create(
        string $title,
        ProjectSlug $slug,
        ?string $description,
        int $sortOrder,
        ?string $imagePath = null,
        ?string $ownerId = null,
    ): self {
        $trimmedTitle = trim($title);
        if ($trimmedTitle === '') {
            throw new InvalidArgumentException('Project title cannot be empty.');
        }

        return new self(
            ProjectId::generate(),
            $trimmedTitle,
            $slug,
            self::normalizeDescription($description),
            ProjectStatus::Draft,
            $sortOrder,
            $imagePath,
            $ownerId,
        );
    }

    public static function restore(
        ProjectId $id,
        string $title,
        ProjectSlug $slug,
        ?string $description,
        ProjectStatus $status,
        int $sortOrder,
        ?string $imagePath,
        ?string $ownerId,
    ): self {
        return new self($id, $title, $slug, $description, $status, $sortOrder, $imagePath, $ownerId);
    }

    public function update(
        string $title,
        ProjectSlug $slug,
        ?string $description,
        ?string $imagePath,
    ): self {
        $trimmedTitle = trim($title);
        if ($trimmedTitle === '') {
            throw new InvalidArgumentException('Project title cannot be empty.');
        }

        return new self(
            $this->id,
            $trimmedTitle,
            $slug,
            self::normalizeDescription($description),
            $this->status,
            $this->sortOrder,
            $imagePath,
            $this->ownerId,
        );
    }

    public function publish(): self
    {
        if ($this->status === ProjectStatus::Published) {
            return $this;
        }

        return new self(
            $this->id,
            $this->title,
            $this->slug,
            $this->description,
            ProjectStatus::Published,
            $this->sortOrder,
            $this->imagePath,
            $this->ownerId,
        );
    }

    public function archive(): self
    {
        if ($this->status === ProjectStatus::Archived) {
            return $this;
        }

        return new self(
            $this->id,
            $this->title,
            $this->slug,
            $this->description,
            ProjectStatus::Archived,
            $this->sortOrder,
            $this->imagePath,
            $this->ownerId,
        );
    }

    public function withSortOrder(int $sortOrder): self
    {
        return new self(
            $this->id,
            $this->title,
            $this->slug,
            $this->description,
            $this->status,
            $sortOrder,
            $this->imagePath,
            $this->ownerId,
        );
    }

    public function id(): ProjectId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function slug(): ProjectSlug
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): ProjectStatus
    {
        return $this->status;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function imagePath(): ?string
    {
        return $this->imagePath;
    }

    public function ownerId(): ?string
    {
        return $this->ownerId;
    }

    private static function normalizeDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $trimmed = trim($description);

        return $trimmed === '' ? null : $trimmed;
    }
}
