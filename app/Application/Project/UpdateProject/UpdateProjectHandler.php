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

namespace App\Application\Project\UpdateProject;

use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;

final class UpdateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(UpdateProjectCommand $command): void
    {
        $id = ProjectId::fromString($command->projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($command->projectId);
        }

        $slugValue = $command->slug ?? ProjectSlug::normalize($command->title);
        $slug = ProjectSlug::fromString($slugValue);
        $existing = $this->projects->findBySlug($slug);
        if ($existing !== null && $existing->id()->value() !== $project->id()->value()) {
            throw ProjectSlugTakenException::forSlug($slug->value());
        }

        $updated = $project->update(
            $command->title,
            $slug,
            $command->description,
            $command->imagePath,
        );
        $this->projects->save($updated);
    }
}
