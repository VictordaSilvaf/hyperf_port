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

namespace App\Application\Project\GetProject;

use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class GetProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(string $projectId, bool $withTrashed = false): array
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id, $withTrashed);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        return ['data' => $this->presenter->toDetail($project)];
    }
}
