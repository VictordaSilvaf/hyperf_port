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

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;

final class CreateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(CreateProjectCommand $command): array
    {
        $slugValue = $command->slug ?? ProjectSlug::normalize($command->title);
        $slug = ProjectSlug::fromString($slugValue);
        if ($this->projects->findBySlug($slug) !== null) {
            throw ProjectSlugTakenException::forSlug($slug->value());
        }

        $status = $command->status !== null
            ? ProjectStatus::from($command->status)
            : ProjectStatus::Draft;

        $project = Project::create([
            'title' => $command->title,
            'slug' => $slug,
            'description' => $command->description,
            'content' => $command->content,
            'repository_url' => $command->repositoryUrl,
            'demo_url' => $command->demoUrl,
            'thumbnail' => $command->thumbnail,
            'cover' => $command->cover,
            'status' => $status,
            'featured' => $command->featured,
            'sort_order' => $this->projects->nextSortOrder(),
        ]);

        $this->projects->save($project);
        $this->projects->syncCategories($project->id(), $command->categories);
        $this->projects->syncTechnologies($project->id(), $command->technologies);
        $this->projects->syncTags($project->id(), $command->tags);
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();

        return ['data' => $this->presenter->toDetail($project)];
    }
}
