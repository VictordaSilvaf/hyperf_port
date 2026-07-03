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

namespace App\Application\Project\SyncProjectTaxonomies;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class SyncProjectCategoriesHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    /** @param list<string> $categoryIds */
    public function handle(string $projectId, array $categoryIds): array
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }

        $this->projects->syncCategories($id, $categoryIds);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($project)];
    }
}

final class SyncProjectTechnologiesHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(string $projectId, array $technologyIds): array
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }
        $this->projects->syncTechnologies($id, $technologyIds);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($project)];
    }
}

final class SyncProjectTagsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(string $projectId, array $tagIds): array
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }
        $this->projects->syncTags($id, $tagIds);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($project)];
    }
}
