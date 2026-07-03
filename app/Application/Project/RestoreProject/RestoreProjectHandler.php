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

namespace App\Application\Project\RestoreProject;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class RestoreProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(string $projectId): array
    {
        $id = ProjectId::fromString($projectId);
        if ($this->projects->findById($id, true) === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        $this->projects->restore($id);
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        return ['data' => $this->presenter->toDetail($project)];
    }
}
