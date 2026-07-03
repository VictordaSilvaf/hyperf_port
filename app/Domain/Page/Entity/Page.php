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

namespace App\Domain\Page\Entity;

use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageLayout;
use App\Domain\Page\ValueObject\PageSeo;
use App\Domain\Page\ValueObject\PageSlug;
use App\Domain\Page\ValueObject\PageStatus;
use DateTimeImmutable;
use InvalidArgumentException;

final class Page
{
    private function __construct(
        private readonly PageId $id,
        private readonly string $title,
        private readonly PageSlug $slug,
        private readonly PageLayout $layout,
        private readonly ?PageSeo $seo,
        private readonly bool $isHome,
        private readonly PageStatus $status,
        private readonly ?DateTimeImmutable $publishedAt,
        private readonly int $sortOrder,
    ) {
    }

    /**
     * @param array{
     *   title: string,
     *   slug: PageSlug,
     *   layout?: PageLayout,
     *   seo?: null|PageSeo,
     *   is_home?: bool,
     *   status?: PageStatus,
     *   sort_order: int,
     * } $data
     */
    public static function create(array $data): self
    {
        $title = trim($data['title']);
        if ($title === '') {
            throw new InvalidArgumentException('Page title cannot be empty.');
        }

        return new self(
            PageId::generate(),
            $title,
            $data['slug'],
            $data['layout'] ?? PageLayout::Default,
            $data['seo'] ?? null,
            $data['is_home'] ?? false,
            $data['status'] ?? PageStatus::Draft,
            null,
            $data['sort_order'],
        );
    }

    /**
     * @param array{
     *   id: PageId,
     *   title: string,
     *   slug: PageSlug,
     *   layout: PageLayout,
     *   seo: null|PageSeo,
     *   is_home: bool,
     *   status: PageStatus,
     *   published_at: null|DateTimeImmutable,
     *   sort_order: int,
     * } $data
     */
    public static function restore(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['slug'],
            $data['layout'],
            $data['seo'],
            $data['is_home'],
            $data['status'],
            $data['published_at'],
            $data['sort_order'],
        );
    }

    /** @param array<string, mixed> $changes */
    public function replace(array $changes): self
    {
        return self::restore([
            'id' => $this->id,
            'title' => $changes['title'] ?? $this->title,
            'slug' => $changes['slug'] ?? $this->slug,
            'layout' => $changes['layout'] ?? $this->layout,
            'seo' => array_key_exists('seo', $changes) ? $changes['seo'] : $this->seo,
            'is_home' => $changes['is_home'] ?? $this->isHome,
            'status' => $changes['status'] ?? $this->status,
            'published_at' => array_key_exists('published_at', $changes) ? $changes['published_at'] : $this->publishedAt,
            'sort_order' => $changes['sort_order'] ?? $this->sortOrder,
        ]);
    }

    public function publish(?DateTimeImmutable $publishedAt = null): self
    {
        return $this->replace([
            'status' => PageStatus::Published,
            'published_at' => $publishedAt ?? new DateTimeImmutable(),
        ]);
    }

    public function archive(): self
    {
        return $this->replace(['status' => PageStatus::Archived]);
    }

    public function toDraft(): self
    {
        return $this->replace(['status' => PageStatus::Draft, 'published_at' => null]);
    }

    public function withSortOrder(int $sortOrder): self
    {
        return $this->replace(['sort_order' => $sortOrder]);
    }

    public function duplicateAsDraft(PageSlug $slug, int $sortOrder): self
    {
        return self::create([
            'title' => $this->title . ' (cópia)',
            'slug' => $slug,
            'layout' => $this->layout,
            'seo' => $this->seo,
            'is_home' => false,
            'status' => PageStatus::Draft,
            'sort_order' => $sortOrder,
        ]);
    }

    public function id(): PageId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function slug(): PageSlug
    {
        return $this->slug;
    }

    public function layout(): PageLayout
    {
        return $this->layout;
    }

    public function seo(): ?PageSeo
    {
        return $this->seo;
    }

    public function isHome(): bool
    {
        return $this->isHome;
    }

    public function status(): PageStatus
    {
        return $this->status;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }
}
