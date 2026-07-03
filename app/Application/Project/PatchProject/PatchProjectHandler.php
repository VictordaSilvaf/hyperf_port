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

namespace App\Application\Project\PatchProject;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;
use DateTimeImmutable;

final class PatchProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly ProjectPresenter $presenter,
    ) {
    }

    public function handle(PatchProjectCommand $command): array
    {
        $id = ProjectId::fromString($command->projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($command->projectId);
        }

        $changes = [];
        $c = $command->changes;
        if (array_key_exists('title', $c)) {
            $changes['title'] = (string) $c['title'];
        }
        if (array_key_exists('slug', $c)) {
            $slug = ProjectSlug::fromString((string) $c['slug']);
            $existing = $this->projects->findBySlug($slug);
            if ($existing !== null && $existing->id()->value() !== $project->id()->value()) {
                throw ProjectSlugTakenException::forSlug($slug->value());
            }
            $changes['slug'] = $slug;
        }
        foreach (['description', 'content', 'repository_url', 'demo_url', 'thumbnail', 'cover'] as $field) {
            if (array_key_exists($field, $c)) {
                $changes[$field] = $c[$field];
            }
        }
        if (array_key_exists('featured', $c)) {
            $changes['featured'] = (bool) $c['featured'];
        }
        if (array_key_exists('status', $c)) {
            $changes['status'] = ProjectStatus::from((string) $c['status']);
        }
        if (array_key_exists('published_at', $c) && $c['published_at'] !== null) {
            $changes['published_at'] = new DateTimeImmutable((string) $c['published_at']);
        }
        if (array_key_exists('order', $c)) {
            $changes['sort_order'] = (int) $c['order'];
        }

        $updated = $project->replace($changes);
        $this->projects->save($updated);

        if (array_key_exists('categories', $c) && is_array($c['categories'])) {
            $this->projects->syncCategories($id, array_map('strval', $c['categories']));
        }
        if (array_key_exists('technologies', $c) && is_array($c['technologies'])) {
            $this->projects->syncTechnologies($id, array_map('strval', $c['technologies']));
        }
        if (array_key_exists('tags', $c) && is_array($c['tags'])) {
            $this->projects->syncTags($id, array_map('strval', $c['tags']));
        }

        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($updated)];
    }
}
