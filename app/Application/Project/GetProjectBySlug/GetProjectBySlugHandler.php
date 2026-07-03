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

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\ProjectViewCounterInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectSlug;

final class GetProjectBySlugHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPresenter $presenter,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly ProjectViewCounterInterface $viewCounter,
    ) {
    }

    public function handle(string $slug, bool $trackView = false): array
    {
        $cacheKey = 'slug:' . $slug;
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached) && ! $trackView) {
            return $cached;
        }

        $project = $this->projects->findBySlug(ProjectSlug::fromString($slug), true);
        if ($project === null) {
            throw ProjectNotFoundException::bySlug($slug);
        }

        if ($trackView) {
            $this->viewCounter->increment($project->id()->value());
        }

        $payload = ['data' => $this->presenter->toDetail($project)];
        $this->cache->set($cacheKey, $payload, 300);

        return $payload;
    }
}
