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

namespace App\Application\Project\CreateProject;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectSlug;

final class CreateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(CreateProjectCommand $command): string
    {
        $slugValue = $command->slug ?? ProjectSlug::normalize($command->title);
        $slug = ProjectSlug::fromString($slugValue);

        if ($this->projects->findBySlug($slug) !== null) {
            throw ProjectSlugTakenException::forSlug($slug->value());
        }

        $project = Project::create(
            $command->title,
            $slug,
            $command->description,
            $this->projects->nextSortOrder(),
            $command->imagePath,
            $command->ownerId,
        );
        $this->projects->save($project);

        return $project->id()->value();
    }
}
