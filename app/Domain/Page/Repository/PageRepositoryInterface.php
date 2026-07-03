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

namespace App\Domain\Page\Repository;

use App\Domain\Page\Entity\Page;
use App\Domain\Page\Entity\PageBlock;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageSlug;

interface PageRepositoryInterface
{
    public function save(Page $page): void;

    public function softDelete(PageId $id): void;

    public function restore(PageId $id): void;

    public function forceDelete(PageId $id): void;

    public function findById(PageId $id, bool $withTrashed = false): ?Page;

    public function findBySlug(PageSlug $slug, bool $publicOnly = false): ?Page;

    public function findHomePage(bool $publicOnly = false): ?Page;

    public function clearHomeFlag(?PageId $exceptId = null): void;

    public function nextSortOrder(): int;

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function paginate(int $page, int $perPage, bool $publicOnly = false): array;

    /** @return list<PageBlock> */
    public function blocksFor(PageId $pageId): array;

    /**
     * @param list<array{type: string, payload: array<string, mixed>, settings?: null|array<string, mixed>}> $blocks
     */
    public function syncBlocks(PageId $pageId, array $blocks): void;

    /**
     * @param list<array{id: string, sort_order: int}> $items
     */
    public function reorderPages(array $items): void;
}
