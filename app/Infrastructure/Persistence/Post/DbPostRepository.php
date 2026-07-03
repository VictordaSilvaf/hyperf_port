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

namespace App\Infrastructure\Persistence\Post;

use App\Domain\Post\Entity\Post;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Post\ValueObject\PostId;
use App\Domain\Post\ValueObject\PostStatus;
use App\Domain\Project\ValueObject\ProjectId;
use Hyperf\DbConnection\Db;

final class DbPostRepository implements PostRepositoryInterface
{
    private const TABLE = 'posts';

    public function save(Post $post): void
    {
        $row = PostPersistenceMapper::toRow($post);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            Db::table(self::TABLE)->where('id', $row['id'])->update([
                'title' => $row['title'],
                'body' => $row['body'],
                'status' => $row['status'],
                'updated_at' => $row['updated_at'],
            ]);
        } else {
            Db::table(self::TABLE)->insert($row);
        }
    }

    public function delete(PostId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->delete();
    }

    public function findById(PostId $id): ?Post
    {
        $row = Db::table(self::TABLE)->where('id', $id->value())->first();

        return $row === null ? null : PostPersistenceMapper::toDomain((array) $row);
    }

    public function paginatedByProject(
        ProjectId $projectId,
        int $page,
        int $perPage,
        ?PostStatus $status = null,
    ): array {
        $builder = Db::table(self::TABLE)
            ->where('project_id', $projectId->value())
            ->select(['id', 'project_id', 'title', 'status', 'created_at', 'updated_at']);
        if ($status !== null) {
            $builder->where('status', $status->value);
        }

        $total = (clone $builder)->count();
        $rows = $builder->orderByDesc('created_at')->forPage($page, $perPage)->get();

        $items = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $items[] = [
                'id' => (string) $data['id'],
                'project_id' => (string) $data['project_id'],
                'title' => (string) $data['title'],
                'status' => (string) $data['status'],
                'created_at' => isset($data['created_at']) ? (string) $data['created_at'] : null,
                'updated_at' => isset($data['updated_at']) ? (string) $data['updated_at'] : null,
            ];
        }

        return ['total' => (int) $total, 'items' => $items];
    }
}
