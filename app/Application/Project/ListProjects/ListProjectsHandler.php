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

namespace App\Application\Project\ListProjects;

use App\Domain\Project\Repository\ProjectRepositoryInterface;

final class ListProjectsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function handle(ListProjectsQuery $query): array
    {
        return $this->projects->paginatedSummaries(
            $query->page,
            $query->perPage,
            $query->search,
            $query->status,
        );
    }
}
