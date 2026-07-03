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
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageBlockId;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageSlug;
use App\Domain\Page\ValueObject\PageStatus;
use Hyperf\DbConnection\Db;

final class DbPageRepository implements PageRepositoryInterface
{
    private const TABLE = 'pages';

    private const BLOCKS_TABLE = 'page_blocks';

    public function save(Page $page): void
    {
        $row = PagePersistenceMapper::toRow($page);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            unset($row['created_at']);
            Db::table(self::TABLE)->where('id', $row['id'])->update($row);
        } else {
            Db::table(self::TABLE)->insert($row);
        }
    }

    public function softDelete(PageId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function restore(PageId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->update(['deleted_at' => null]);
    }

    public function forceDelete(PageId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->delete();
    }

    public function findById(PageId $id, bool $withTrashed = false): ?Page
    {
        $builder = Db::table(self::TABLE)->where('id', $id->value());
        if (! $withTrashed) {
            $builder->whereNull('deleted_at');
        }
        $row = $builder->first();

        return $row === null ? null : PagePersistenceMapper::toDomain((array) $row);
    }

    public function findBySlug(PageSlug $slug, bool $publicOnly = false): ?Page
    {
        $builder = Db::table(self::TABLE)->where('slug', $slug->value())->whereNull('deleted_at');
        if ($publicOnly) {
            $builder->where('status', PageStatus::Published->value);
        }
        $row = $builder->first();

        return $row === null ? null : PagePersistenceMapper::toDomain((array) $row);
    }

    public function findHomePage(bool $publicOnly = false): ?Page
    {
        $builder = Db::table(self::TABLE)->where('is_home', true)->whereNull('deleted_at');
        if ($publicOnly) {
            $builder->where('status', PageStatus::Published->value);
        }
        $row = $builder->first();

        return $row === null ? null : PagePersistenceMapper::toDomain((array) $row);
    }

    public function clearHomeFlag(?PageId $exceptId = null): void
    {
        $builder = Db::table(self::TABLE)->where('is_home', true);
        if ($exceptId !== null) {
            $builder->where('id', '!=', $exceptId->value());
        }
        $builder->update(['is_home' => false, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function nextSortOrder(): int
    {
        $max = Db::table(self::TABLE)->max('sort_order');

        return $max === null ? 1 : ((int) $max) + 1;
    }

    public function paginate(int $page, int $perPage, bool $publicOnly = false): array
    {
        $builder = Db::table(self::TABLE)->whereNull('deleted_at');
        if ($publicOnly) {
            $builder->where('status', PageStatus::Published->value);
        }

        $total = (clone $builder)->count();
        $rows = $builder->orderBy('sort_order')
            ->forPage($page, $perPage)
            ->get();

        return [
            'total' => (int) $total,
            'items' => array_map(fn ($row) => $this->summaryFromRow((array) $row), $rows->all()),
        ];
    }

    public function blocksFor(PageId $pageId): array
    {
        $rows = Db::table(self::BLOCKS_TABLE)
            ->where('page_id', $pageId->value())
            ->orderBy('sort_order')
            ->get();

        return array_map(
            static fn ($row) => PagePersistenceMapper::blockToDomain((array) $row),
            $rows->all()
        );
    }

    public function syncBlocks(PageId $pageId, array $blocks): void
    {
        Db::table(self::BLOCKS_TABLE)->where('page_id', $pageId->value())->delete();

        $now = date('Y-m-d H:i:s');
        foreach ($blocks as $index => $block) {
            Db::table(self::BLOCKS_TABLE)->insert([
                'id' => PageBlockId::generate()->value(),
                'page_id' => $pageId->value(),
                'type' => (string) $block['type'],
                'sort_order' => $index,
                'payload' => json_encode($block['payload'], JSON_THROW_ON_ERROR),
                'settings' => isset($block['settings']) && $block['settings'] !== null
                    ? json_encode($block['settings'], JSON_THROW_ON_ERROR)
                    : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function reorderPages(array $items): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            Db::table(self::TABLE)
                ->where('id', (string) $item['id'])
                ->update(['sort_order' => (int) $item['sort_order'], 'updated_at' => $now]);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function summaryFromRow(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'title' => (string) $row['title'],
            'slug' => (string) $row['slug'],
            'status' => (string) $row['status'],
            'is_home' => (bool) ($row['is_home'] ?? false),
            'sort_order' => (int) $row['sort_order'],
            'published_at' => isset($row['published_at']) ? (string) $row['published_at'] : null,
        ];
    }
}
