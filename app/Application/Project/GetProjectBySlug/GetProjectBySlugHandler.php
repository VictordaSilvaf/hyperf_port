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

namespace App\Application\Project\GetProjectBySlug;

use App\Application\Project\GetProject\GetProjectResult;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectSlug;

final class GetProjectBySlugHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(GetProjectBySlugQuery $query): GetProjectResult
    {
        $slug = ProjectSlug::fromString($query->slug);
        $project = $this->projects->findBySlug($slug);
        if ($project === null || ($query->publicOnly && ! $project->status()->isPublic())) {
            throw ProjectNotFoundException::bySlug($query->slug);
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
