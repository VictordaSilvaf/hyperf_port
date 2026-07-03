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
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageSlug;

final class InMemoryPageRepository implements PageRepositoryInterface
{
    /** @var array<string, Page> */
    private array $items = [];

    /** @var array<string, true> */
    private array $trashed = [];

    /** @var array<string, PageBlock> */
    private array $blocks = [];

    public function save(Page $page): void
    {
        $this->items[$page->id()->value()] = $page;
    }

    public function softDelete(PageId $id): void
    {
        $this->trashed[$id->value()] = true;
    }

    public function restore(PageId $id): void
    {
        unset($this->trashed[$id->value()]);
    }

    public function forceDelete(PageId $id): void
    {
        $pageId = $id->value();
        unset($this->items[$pageId], $this->trashed[$pageId]);
        $this->blocks = array_filter(
            $this->blocks,
            static fn (PageBlock $block): bool => $block->pageId()->value() !== $pageId
        );
    }

    public function findById(PageId $id, bool $withTrashed = false): ?Page
    {
        if (! isset($this->items[$id->value()])) {
            return null;
        }
        if (! $withTrashed && isset($this->trashed[$id->value()])) {
            return null;
        }

        return $this->items[$id->value()];
    }

    public function findBySlug(PageSlug $slug, bool $publicOnly = false): ?Page
    {
        foreach ($this->items as $id => $page) {
            if (isset($this->trashed[$id])) {
                continue;
            }
            if ($page->slug()->value() === $slug->value()) {
                if ($publicOnly && ! $page->status()->isPublic()) {
                    return null;
                }

                return $page;
            }
        }

        return null;
    }

    public function findHomePage(bool $publicOnly = false): ?Page
    {
        foreach ($this->items as $id => $page) {
            if (isset($this->trashed[$id])) {
                continue;
            }
            if (! $page->isHome()) {
                continue;
            }
            if ($publicOnly && ! $page->status()->isPublic()) {
                return null;
            }

            return $page;
        }

        return null;
    }

    public function clearHomeFlag(?PageId $exceptId = null): void
    {
        foreach ($this->items as $id => $page) {
            if (! $page->isHome()) {
                continue;
            }
            if ($exceptId !== null && $page->id()->value() === $exceptId->value()) {
                continue;
            }
            $this->items[$id] = $page->replace(['is_home' => false]);
        }
    }

    public function nextSortOrder(): int
    {
        if ($this->items === []) {
            return 1;
        }

        return max(array_map(static fn (Page $page): int => $page->sortOrder(), $this->items)) + 1;
    }

    public function paginate(int $page, int $perPage, bool $publicOnly = false): array
    {
        $list = [];
        foreach ($this->items as $id => $item) {
            if (isset($this->trashed[$id])) {
                continue;
            }
            if ($publicOnly && ! $item->status()->isPublic()) {
                continue;
            }
            $list[] = $item;
        }

        usort($list, static fn (Page $a, Page $b): int => $a->sortOrder() <=> $b->sortOrder());

        $total = count($list);
        $offset = max(0, ($page - 1) * $perPage);
        $slice = array_slice($list, $offset, $perPage);

        return [
            'total' => $total,
            'items' => array_map(static fn (Page $item): array => [
                'id' => $item->id()->value(),
                'title' => $item->title(),
                'slug' => $item->slug()->value(),
                'status' => $item->status()->value,
                'is_home' => $item->isHome(),
                'sort_order' => $item->sortOrder(),
                'published_at' => $item->publishedAt()?->format('Y-m-d H:i:s'),
            ], $slice),
        ];
    }

    public function blocksFor(PageId $pageId): array
    {
        $blocks = array_values(array_filter(
            $this->blocks,
            static fn (PageBlock $block): bool => $block->pageId()->value() === $pageId->value()
        ));

        usort($blocks, static fn (PageBlock $a, PageBlock $b): int => $a->sortOrder() <=> $b->sortOrder());

        return $blocks;
    }

    public function syncBlocks(PageId $pageId, array $blocks): void
    {
        $this->blocks = array_filter(
            $this->blocks,
            static fn (PageBlock $block): bool => $block->pageId()->value() !== $pageId->value()
        );

        foreach ($blocks as $index => $block) {
            $entity = PageBlock::create(
                $pageId,
                (string) $block['type'],
                $index,
                $block['payload'],
                $block['settings'] ?? null,
            );
            $this->blocks[$entity->id()->value()] = $entity;
        }
    }

    public function reorderPages(array $items): void
    {
        foreach ($items as $item) {
            $page = $this->findById(PageId::fromString((string) $item['id']), true);
            if ($page === null) {
                continue;
            }
            $this->items[$page->id()->value()] = $page->withSortOrder((int) $item['sort_order']);
        }
    }
}
