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

namespace App\Application\Project\ReorderProjects;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class ReorderProjectsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
    ) {
    }

    /** @param list<array{id: string, order: int}> $items */
    public function handle(array $items): void
    {
        foreach ($items as $item) {
            $id = ProjectId::fromString((string) $item['id']);
            $project = $this->projects->findById($id);
            if ($project === null) {
                throw ProjectNotFoundException::byId((string) $item['id']);
            }
            $this->projects->save($project->withSortOrder((int) $item['order']));
        }
        $this->cache->bump();
    }
}
