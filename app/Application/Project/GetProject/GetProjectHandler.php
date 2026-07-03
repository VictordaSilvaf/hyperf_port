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

use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class GetProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(GetProjectQuery $query): GetProjectResult
    {
        $id = ProjectId::fromString($query->projectId);
        $project = $this->projects->findById($id);
        if ($project === null || ($query->publicOnly && ! $project->status()->isPublic())) {
            throw ProjectNotFoundException::byId($query->projectId);
        }

        return new GetProjectResult(
            $project->id()->value(),
            $project->title(),
            $project->slug()->value(),
            $project->description(),
            $project->status()->value,
            $project->sortOrder(),
            $project->imagePath(),
            $project->ownerId(),
        );
    }
}
