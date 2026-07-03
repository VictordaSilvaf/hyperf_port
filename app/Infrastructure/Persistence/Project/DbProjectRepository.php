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

namespace App\Infrastructure\Persistence\Project;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;
use Hyperf\DbConnection\Db;

final class DbProjectRepository implements ProjectRepositoryInterface
{
    private const TABLE = 'projects';

    public function save(Project $project): void
    {
        $row = ProjectPersistenceMapper::toRow($project);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            Db::table(self::TABLE)->where('id', $row['id'])->update([
                'title' => $row['title'],
                'slug' => $row['slug'],
                'description' => $row['description'],
                'status' => $row['status'],
                'sort_order' => $row['sort_order'],
                'image_path' => $row['image_path'],
                'owner_id' => $row['owner_id'],
                'updated_at' => $row['updated_at'],
            ]);
        } else {
            Db::table(self::TABLE)->insert($row);
        }
    }

    public function delete(ProjectId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->delete();
    }

    public function findById(ProjectId $id): ?Project
    {
        $row = Db::table(self::TABLE)->where('id', $id->value())->first();

        return $row === null ? null : ProjectPersistenceMapper::toDomain((array) $row);
    }

    public function findBySlug(ProjectSlug $slug): ?Project
    {
        $row = Db::table(self::TABLE)->where('slug', $slug->value())->first();

        return $row === null ? null : ProjectPersistenceMapper::toDomain((array) $row);
    }

    public function nextSortOrder(): int
    {
        $max = Db::table(self::TABLE)->max('sort_order');

        return $max === null ? 0 : ((int) $max) + 1;
    }

    public function paginatedSummaries(
        int $page,
        int $perPage,
        ?string $search = null,
        ?ProjectStatus $status = null,
    ): array {
        $builder = Db::table(self::TABLE)->select([
            'id', 'title', 'slug', 'status', 'sort_order', 'image_path', 'created_at', 'updated_at',
        ]);
        $trimmedSearch = $search !== null ? trim($search) : '';
        if ($trimmedSearch !== '') {
            $term = '%' . addcslashes($trimmedSearch, '%_\\') . '%';
            $builder->where(static function ($q) use ($term): void {
                $q->where('title', 'ilike', $term)->orWhere('slug', 'ilike', $term);
            });
        }
        if ($status !== null) {
            $builder->where('status', $status->value);
        }

        $total = (clone $builder)->count();
        $rows = $builder->orderBy('sort_order')->orderBy('title')->forPage($page, $perPage)->get();

        $items = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $items[] = [
                'id' => (string) $data['id'],
                'title' => (string) $data['title'],
                'slug' => (string) $data['slug'],
                'status' => (string) $data['status'],
                'sort_order' => (int) $data['sort_order'],
                'image_path' => isset($data['image_path']) ? (string) $data['image_path'] : null,
                'created_at' => isset($data['created_at']) ? (string) $data['created_at'] : null,
                'updated_at' => isset($data['updated_at']) ? (string) $data['updated_at'] : null,
            ];
        }

        return ['total' => (int) $total, 'items' => $items];
    }
}
