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

namespace App\Application\Project\SearchProjects;

use App\Application\Project\Shared\ProjectMediaUrls;
use App\Domain\Project\Repository\ProjectRepositoryInterface;

final class SearchProjectsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectMediaUrls $mediaUrls,
    ) {
    }

    public function handle(string $query, int $page = 1, int $perPage = 15): array
    {
        $result = $this->projects->search(trim($query), $page, $perPage);

        return ['projects' => $this->mediaUrls->enrichMany($result['items']), 'meta' => [
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ]];
    }
}
