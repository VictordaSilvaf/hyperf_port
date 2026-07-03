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

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;

final class UpdateProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(UpdateProjectCommand $command): array
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

        $updated = $project->replace([
            'title' => $command->title,
            'slug' => $slug,
            'description' => $command->description,
            'content' => $command->content,
            'repository_url' => $command->repositoryUrl,
            'demo_url' => $command->demoUrl,
            'thumbnail' => $command->thumbnail,
            'cover' => $command->cover,
            'status' => $command->status !== null ? ProjectStatus::from($command->status) : $project->status(),
            'featured' => $command->featured,
        ]);

        $this->projects->save($updated);
        $this->projects->syncCategories($id, $command->categories);
        $this->projects->syncTechnologies($id, $command->technologies);
        $this->projects->syncTags($id, $command->tags);
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();

        return ['data' => $this->presenter->toDetail($updated)];
    }
}
