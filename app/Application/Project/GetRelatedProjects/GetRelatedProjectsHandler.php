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

namespace App\Application\Project\GetRelatedProjects;

use App\Application\Project\Shared\ProjectMediaUrls;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;

final class GetRelatedProjectsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectMediaUrls $mediaUrls,
    ) {
    }

    public function handle(string $slug): array
    {
        $project = $this->projects->findBySlug(ProjectSlug::fromString($slug), true);
        if ($project === null) {
            throw ProjectNotFoundException::bySlug($slug);
        }

        return ['data' => $this->mediaUrls->enrichMany(
            $this->projects->relatedPublished(ProjectId::fromString($project->id()->value())),
        )];
    }
}
