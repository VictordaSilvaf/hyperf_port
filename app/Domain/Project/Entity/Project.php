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
use DateTimeImmutable;
use InvalidArgumentException;

final class Project
{
    private function __construct(
        private readonly ProjectId $id,
        private readonly string $title,
        private readonly ProjectSlug $slug,
        private readonly ?string $description,
        private readonly ?string $content,
        private readonly ?string $repositoryUrl,
        private readonly ?string $demoUrl,
        private readonly ?string $thumbnailPath,
        private readonly ?string $coverPath,
        private readonly ProjectStatus $status,
        private readonly bool $featured,
        private readonly ?DateTimeImmutable $publishedAt,
        private readonly int $sortOrder,
        private readonly int $views,
        private readonly ?string $ownerId,
    ) {
    }

    /**
     * @param array{
     *   title: string,
     *   slug: ProjectSlug,
     *   description?: null|string,
     *   content?: null|string,
     *   repository_url?: null|string,
     *   demo_url?: null|string,
     *   thumbnail?: null|string,
     *   cover?: null|string,
     *   status?: ProjectStatus,
     *   featured?: bool,
     *   sort_order: int,
     * } $data
     */
    public static function create(array $data): self
    {
        $title = trim($data['title']);
        if ($title === '') {
            throw new InvalidArgumentException('Project title cannot be empty.');
        }

        return new self(
            ProjectId::generate(),
            $title,
            $data['slug'],
            self::nullableString($data['description'] ?? null),
            self::nullableString($data['content'] ?? null),
            self::nullableString($data['repository_url'] ?? null),
            self::nullableString($data['demo_url'] ?? null),
            self::nullableString($data['thumbnail'] ?? null),
            self::nullableString($data['cover'] ?? null),
            $data['status'] ?? ProjectStatus::Draft,
            $data['featured'] ?? false,
            null,
            $data['sort_order'],
            0,
            null,
        );
    }

    /**
     * @param array{
     *   id: ProjectId,
     *   title: string,
     *   slug: ProjectSlug,
     *   description: null|string,
     *   content: null|string,
     *   repository_url: null|string,
     *   demo_url: null|string,
     *   thumbnail: null|string,
     *   cover: null|string,
     *   status: ProjectStatus,
     *   featured: bool,
     *   published_at: null|DateTimeImmutable,
     *   sort_order: int,
     *   views: int,
     *   owner_id: null|string,
     * } $data
     */
    public static function restore(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['content'],
            $data['repository_url'],
            $data['demo_url'],
            $data['thumbnail'],
            $data['cover'],
            $data['status'],
            $data['featured'],
            $data['published_at'],
            $data['sort_order'],
            $data['views'],
            $data['owner_id'],
        );
    }

    public function replace(array $changes): self
    {
        return self::restore([
            'id' => $this->id,
            'title' => $changes['title'] ?? $this->title,
            'slug' => $changes['slug'] ?? $this->slug,
            'description' => array_key_exists('description', $changes) ? self::nullableString($changes['description']) : $this->description,
            'content' => array_key_exists('content', $changes) ? self::nullableString($changes['content']) : $this->content,
            'repository_url' => array_key_exists('repository_url', $changes) ? self::nullableString($changes['repository_url']) : $this->repositoryUrl,
            'demo_url' => array_key_exists('demo_url', $changes) ? self::nullableString($changes['demo_url']) : $this->demoUrl,
            'thumbnail' => array_key_exists('thumbnail', $changes) ? self::nullableString($changes['thumbnail']) : $this->thumbnailPath,
            'cover' => array_key_exists('cover', $changes) ? self::nullableString($changes['cover']) : $this->coverPath,
            'status' => $changes['status'] ?? $this->status,
            'featured' => $changes['featured'] ?? $this->featured,
            'published_at' => array_key_exists('published_at', $changes) ? $changes['published_at'] : $this->publishedAt,
            'sort_order' => $changes['sort_order'] ?? $this->sortOrder,
            'views' => $changes['views'] ?? $this->views,
            'owner_id' => array_key_exists('owner_id', $changes) ? $changes['owner_id'] : $this->ownerId,
        ]);
    }

    public function publish(?DateTimeImmutable $publishedAt = null): self
    {
        return $this->replace([
            'status' => ProjectStatus::Published,
            'published_at' => $publishedAt ?? new DateTimeImmutable(),
        ]);
    }

    public function archive(): self
    {
        return $this->replace(['status' => ProjectStatus::Archived]);
    }

    public function toDraft(): self
    {
        return $this->replace(['status' => ProjectStatus::Draft, 'published_at' => null]);
    }

    public function withSortOrder(int $sortOrder): self
    {
        return $this->replace(['sort_order' => $sortOrder]);
    }

    public function duplicateAsDraft(ProjectSlug $slug, int $sortOrder): self
    {
        return self::create([
            'title' => $this->title . ' (cópia)',
            'slug' => $slug,
            'description' => $this->description,
            'content' => $this->content,
            'repository_url' => $this->repositoryUrl,
            'demo_url' => $this->demoUrl,
            'thumbnail' => $this->thumbnailPath,
            'cover' => $this->coverPath,
            'status' => ProjectStatus::Draft,
            'featured' => false,
            'sort_order' => $sortOrder,
        ]);
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

    public function content(): ?string
    {
        return $this->content;
    }

    public function repositoryUrl(): ?string
    {
        return $this->repositoryUrl;
    }

    public function demoUrl(): ?string
    {
        return $this->demoUrl;
    }

    public function thumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function coverPath(): ?string
    {
        return $this->coverPath;
    }

    public function status(): ProjectStatus
    {
        return $this->status;
    }

    public function featured(): bool
    {
        return $this->featured;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function views(): int
    {
        return $this->views;
    }

    public function ownerId(): ?string
    {
        return $this->ownerId;
    }

    private static function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
