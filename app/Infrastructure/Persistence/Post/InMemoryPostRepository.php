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

final class InMemoryPostRepository implements PostRepositoryInterface
{
    /** @var array<string, Post> */
    private array $items = [];

    public function save(Post $post): void
    {
        $this->items[$post->id()->value()] = $post;
    }

    public function delete(PostId $id): void
    {
        unset($this->items[$id->value()]);
    }

    public function findById(PostId $id): ?Post
    {
        return $this->items[$id->value()] ?? null;
    }

    public function paginatedByProject(
        ProjectId $projectId,
        int $page,
        int $perPage,
        ?PostStatus $status = null,
    ): array {
        $list = array_values(array_filter(
            $this->items,
            static fn (Post $p): bool => $p->projectId()->value() === $projectId->value()
        ));
        if ($status !== null) {
            $list = array_values(array_filter(
                $list,
                static fn (Post $p): bool => $p->status() === $status
            ));
        }

        $total = count($list);
        $offset = max(0, ($page - 1) * $perPage);
        $slice = array_slice($list, $offset, $perPage);

        return [
            'total' => $total,
            'items' => array_map(static fn (Post $p): array => [
                'id' => $p->id()->value(),
                'project_id' => $p->projectId()->value(),
                'title' => $p->title(),
                'status' => $p->status()->value,
            ], $slice),
        ];
    }
}
