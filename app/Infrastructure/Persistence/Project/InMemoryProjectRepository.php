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

namespace App\Infrastructure\Persistence\Project;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Entity\ProjectImage;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;

final class InMemoryProjectRepository implements ProjectRepositoryInterface
{
    /** @var array<string, Project> */
    private array $items = [];

    /** @var array<string, true> */
    private array $trashed = [];

    /** @var array<string, list<string>> */
    private array $categories = [];

    /** @var array<string, list<string>> */
    private array $technologies = [];

    /** @var array<string, list<string>> */
    private array $tags = [];

    /** @var array<string, ProjectImage> */
    private array $images = [];

    public function save(Project $project): void
    {
        $this->items[$project->id()->value()] = $project;
    }

    public function softDelete(ProjectId $id): void
    {
        $this->trashed[$id->value()] = true;
    }

    public function restore(ProjectId $id): void
    {
        unset($this->trashed[$id->value()]);
    }

    public function forceDelete(ProjectId $id): void
    {
        unset($this->items[$id->value()], $this->trashed[$id->value()]);
    }

    public function findById(ProjectId $id, bool $withTrashed = false): ?Project
    {
        if (! isset($this->items[$id->value()])) {
            return null;
        }
        if (! $withTrashed && isset($this->trashed[$id->value()])) {
            return null;
        }

        return $this->items[$id->value()];
    }

    public function findBySlug(ProjectSlug $slug, bool $publicOnly = false): ?Project
    {
        foreach ($this->items as $id => $project) {
            if (isset($this->trashed[$id])) {
                continue;
            }
            if ($project->slug()->value() === $slug->value()) {
                if ($publicOnly && ! $project->status()->isPublic()) {
                    return null;
                }

                return $project;
            }
        }

        return null;
    }

    public function nextSortOrder(): int
    {
        if ($this->items === []) {
            return 1;
        }

        return max(array_map(static fn (Project $p): int => $p->sortOrder(), $this->items)) + 1;
    }

    public function incrementViews(ProjectId $id, int $amount): void
    {
        $project = $this->findById($id, true);
        if ($project === null) {
            return;
        }
        $this->items[$id->value()] = $project->replace(['views' => $project->views() + $amount]);
    }

    public function paginate(ProjectListFilter $filter): array
    {
        $list = [];
        foreach ($this->items as $id => $project) {
            if (isset($this->trashed[$id]) && ! $filter->withTrashed) {
                continue;
            }
            if ($filter->publicOnly && ! $project->status()->isPublic()) {
                continue;
            }
            if ($filter->status !== null && $project->status() !== $filter->status) {
                continue;
            }
            if ($filter->featured !== null && $project->featured() !== $filter->featured) {
                continue;
            }
            if ($filter->search !== null && trim($filter->search) !== '') {
                $needle = strtolower(trim($filter->search));
                $haystack = strtolower($project->title() . ' ' . $project->slug()->value() . ' ' . ($project->description() ?? '') . ' ' . ($project->content() ?? ''));
                if (! str_contains($haystack, $needle)) {
                    continue;
                }
            }
            $list[] = $project;
        }

        return [
            'total' => count($list),
            'items' => array_map(static fn (Project $p): array => [
                'id' => $p->id()->value(),
                'title' => $p->title(),
                'slug' => $p->slug()->value(),
                'status' => $p->status()->value,
                'featured' => $p->featured(),
                'order' => $p->sortOrder(),
            ], $list),
        ];
    }

    public function statistics(): array
    {
        $published = $draft = $archived = $featured = $views = 0;
        foreach ($this->items as $id => $project) {
            if (isset($this->trashed[$id])) {
                continue;
            }
            match ($project->status()) {
                ProjectStatus::Published => ++$published,
                ProjectStatus::Draft => ++$draft,
                ProjectStatus::Archived => ++$archived,
            };
            if ($project->featured()) {
                ++$featured;
            }
            $views += $project->views();
        }

        return compact('published', 'draft', 'archived', 'featured', 'views');
    }

    public function syncCategories(ProjectId $projectId, array $categoryIds): void
    {
        $this->categories[$projectId->value()] = $categoryIds;
    }

    public function syncTechnologies(ProjectId $projectId, array $technologyIds): void
    {
        $this->technologies[$projectId->value()] = $technologyIds;
    }

    public function syncTags(ProjectId $projectId, array $tagIds): void
    {
        $this->tags[$projectId->value()] = $tagIds;
    }

    public function categoryIdsFor(ProjectId $projectId): array
    {
        return $this->categories[$projectId->value()] ?? [];
    }

    public function technologyIdsFor(ProjectId $projectId): array
    {
        return $this->technologies[$projectId->value()] ?? [];
    }

    public function tagIdsFor(ProjectId $projectId): array
    {
        return $this->tags[$projectId->value()] ?? [];
    }

    public function saveImage(ProjectImage $image): void
    {
        $this->images[$image->id()->value()] = $image;
    }

    public function deleteImage(ProjectImageId $imageId): void
    {
        unset($this->images[$imageId->value()]);
    }

    public function findImageById(ProjectImageId $imageId): ?ProjectImage
    {
        return $this->images[$imageId->value()] ?? null;
    }

    public function imagesFor(ProjectId $projectId): array
    {
        return array_values(array_filter(
            $this->images,
            static fn (ProjectImage $i): bool => $i->projectId()->value() === $projectId->value()
        ));
    }

    public function reorderImages(ProjectId $projectId, array $items): void
    {
        foreach ($items as $item) {
            $image = $this->findImageById(ProjectImageId::fromString((string) $item['id']));
            if ($image !== null) {
                $this->images[$image->id()->value()] = $image->withSortOrder((int) $item['sort_order']);
            }
        }
    }

    public function relatedPublished(ProjectId $projectId, int $limit = 6): array
    {
        return [];
    }

    public function search(string $query, int $page, int $perPage): array
    {
        return $this->paginate(new ProjectListFilter(page: $page, perPage: $perPage, search: $query, publicOnly: true));
    }
}
