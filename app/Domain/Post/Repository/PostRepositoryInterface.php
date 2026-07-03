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

namespace App\Domain\Post\Repository;

use App\Domain\Post\Entity\Post;
use App\Domain\Post\ValueObject\PostId;
use App\Domain\Post\ValueObject\PostStatus;
use App\Domain\Project\ValueObject\ProjectId;

interface PostRepositoryInterface
{
    public function save(Post $post): void;

    public function delete(PostId $id): void;

    public function findById(PostId $id): ?Post;

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function paginatedByProject(
        ProjectId $projectId,
        int $page,
        int $perPage,
        ?PostStatus $status = null,
    ): array;
}
