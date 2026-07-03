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

namespace App\Application\Project\DuplicateProject;

use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;

final class DuplicateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(string $projectId): array
    {
        $id = ProjectId::fromString($projectId);
        $source = $this->projects->findById($id);
        if ($source === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        $baseSlug = $source->slug()->value() . '-copy';
        $slug = ProjectSlug::fromString($baseSlug);
        $attempt = 1;
        while ($this->projects->findBySlug($slug) !== null) {
            ++$attempt;
            if ($attempt > 20) {
                throw ProjectSlugTakenException::forSlug($baseSlug);
            }
            $slug = ProjectSlug::fromString($baseSlug . '-' . $attempt);
        }

        $copy = $source->duplicateAsDraft($slug, $this->projects->nextSortOrder());
        $this->projects->save($copy);
        $this->projects->syncCategories($copy->id(), $this->projects->categoryIdsFor($id));
        $this->projects->syncTechnologies($copy->id(), $this->projects->technologyIdsFor($id));
        $this->projects->syncTags($copy->id(), $this->projects->tagIdsFor($id));

        return ['data' => $this->presenter->toDetail($copy)];
    }
}
