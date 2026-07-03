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

namespace App\Domain\Project\Repository;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Entity\ProjectImage;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectSlug;

interface ProjectRepositoryInterface
{
    public function save(Project $project): void;

    public function softDelete(ProjectId $id): void;

    public function restore(ProjectId $id): void;

    public function forceDelete(ProjectId $id): void;

    public function findById(ProjectId $id, bool $withTrashed = false): ?Project;

    public function findBySlug(ProjectSlug $slug, bool $publicOnly = false): ?Project;

    public function nextSortOrder(): int;

    public function incrementViews(ProjectId $id, int $amount): void;

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function paginate(ProjectListFilter $filter): array;

    /**
     * @return array{published: int, draft: int, archived: int, views: int, featured: int}
     */
    public function statistics(): array;

    /** @param list<string> $categoryIds */
    public function syncCategories(ProjectId $projectId, array $categoryIds): void;

    /** @param list<string> $technologyIds */
    public function syncTechnologies(ProjectId $projectId, array $technologyIds): void;

    /** @param list<string> $tagIds */
    public function syncTags(ProjectId $projectId, array $tagIds): void;

    /** @return list<string> */
    public function categoryIdsFor(ProjectId $projectId): array;

    /** @return list<string> */
    public function technologyIdsFor(ProjectId $projectId): array;

    /** @return list<string> */
    public function tagIdsFor(ProjectId $projectId): array;

    public function saveImage(ProjectImage $image): void;

    public function deleteImage(ProjectImageId $imageId): void;

    public function findImageById(ProjectImageId $imageId): ?ProjectImage;

    /** @return list<ProjectImage> */
    public function imagesFor(ProjectId $projectId): array;

    /**
     * @param list<array{id: string, sort_order: int}> $items
     */
    public function reorderImages(ProjectId $projectId, array $items): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function relatedPublished(ProjectId $projectId, int $limit = 6): array;

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function search(string $query, int $page, int $perPage): array;
}
