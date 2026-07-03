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

namespace App\Application\Project\ArchiveProject;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class ArchiveProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(string $projectId): array
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        $updated = $project->archive();
        $this->projects->save($updated);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($updated)];
    }
}
