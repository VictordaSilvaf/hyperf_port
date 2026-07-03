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
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;

interface ProjectRepositoryInterface
{
    public function save(Project $project): void;

    public function delete(ProjectId $id): void;

    public function findById(ProjectId $id): ?Project;

    public function findBySlug(ProjectSlug $slug): ?Project;

    public function nextSortOrder(): int;

    /**
     * @return array{total: int, items: list<array<string, mixed>>}
     */
    public function paginatedSummaries(
        int $page,
        int $perPage,
        ?string $search = null,
        ?ProjectStatus $status = null,
    ): array;
}
