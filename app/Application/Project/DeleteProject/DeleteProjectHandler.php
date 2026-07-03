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

namespace App\Application\Project\DeleteProject;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class DeleteProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function handle(string $projectId, bool $force = false): void
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id, $force);
        if ($project === null && ! $force) {
            $project = $this->projects->findById($id, true);
        }
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        if ($force) {
            $this->projects->forceDelete($id);
        } else {
            $this->projects->softDelete($id);
        }
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();
    }
}
