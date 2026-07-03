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

namespace App\Application\Post\ListPosts;

use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class ListPostsHandler
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function handle(ListPostsQuery $query): array
    {
        $projectId = ProjectId::fromString($query->projectId);
        if ($this->projects->findById($projectId) === null) {
            throw ProjectNotFoundException::byId($query->projectId);
        }

        return $this->posts->paginatedByProject(
            $projectId,
            $query->page,
            $query->perPage,
            $query->status,
        );
    }
}
