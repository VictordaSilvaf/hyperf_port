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

namespace App\Infrastructure\Persistence\Page;

use App\Domain\Page\Entity\Page;
use App\Domain\Page\Entity\PageBlock;
use App\Domain\Page\ValueObject\PageBlockId;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageLayout;
use App\Domain\Page\ValueObject\PageSeo;
use App\Domain\Page\ValueObject\PageSlug;
use App\Domain\Page\ValueObject\PageStatus;
use DateTimeImmutable;

final class PagePersistenceMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toDomain(array $row): Page
    {
        $publishedAt = null;
        if (! empty($row['published_at'])) {
            $publishedAt = new DateTimeImmutable((string) $row['published_at']);
        }

        return Page::restore([
            'id' => PageId::fromString((string) $row['id']),
            'title' => (string) $row['title'],
            'slug' => PageSlug::fromString((string) $row['slug']),
            'layout' => PageLayout::fromString((string) ($row['layout'] ?? PageLayout::Default->value)),
            'seo' => PageSeo::fromArray(self::decodeJson($row['seo'] ?? null)),
            'is_home' => (bool) ($row['is_home'] ?? false),
            'status' => PageStatus::from((string) $row['status']),
            'published_at' => $publishedAt,
            'sort_order' => (int) $row['sort_order'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function toRow(Page $page): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id' => $page->id()->value(),
            'title' => $page->title(),
            'slug' => $page->slug()->value(),
            'status' => $page->status()->value,
            'layout' => $page->layout()->value,
            'seo' => self::encodeJson($page->seo()?->toArray()),
            'is_home' => $page->isHome(),
            'sort_order' => $page->sortOrder(),
            'published_at' => $page->publishedAt()?->format('Y-m-d H:i:s'),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function blockToDomain(array $row): PageBlock
    {
        $payload = self::decodeJson($row['payload'] ?? null) ?? [];
        $settings = self::decodeJson($row['settings'] ?? null);

        return PageBlock::restore([
            'id' => PageBlockId::fromString((string) $row['id']),
            'page_id' => PageId::fromString((string) $row['page_id']),
            'type' => (string) $row['type'],
            'sort_order' => (int) $row['sort_order'],
            'payload' => $payload,
            'settings' => $settings,
        ]);
    }

    /**
     * @return null|array<string, mixed>
     */
    private static function decodeJson(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param null|array<string, mixed> $value
     */
    private static function encodeJson(?array $value): ?string
    {
        if ($value === null || $value === []) {
            return null;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
