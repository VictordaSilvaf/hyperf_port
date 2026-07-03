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
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;

final class InMemoryProjectRepository implements ProjectRepositoryInterface
{
    /** @var array<string, Project> */
    private array $items = [];

    public function save(Project $project): void
    {
        $this->items[$project->id()->value()] = $project;
    }

    public function delete(ProjectId $id): void
    {
        unset($this->items[$id->value()]);
    }

    public function findById(ProjectId $id): ?Project
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findBySlug(ProjectSlug $slug): ?Project
    {
        foreach ($this->items as $project) {
            if ($project->slug()->value() === $slug->value()) {
                return $project;
            }
        }

        return null;
    }

    public function nextSortOrder(): int
    {
        if ($this->items === []) {
            return 0;
        }

        return max(array_map(static fn (Project $p): int => $p->sortOrder(), $this->items)) + 1;
    }

    public function paginatedSummaries(
        int $page,
        int $perPage,
        ?string $search = null,
        ?ProjectStatus $status = null,
    ): array {
        $list = array_values($this->items);
        $trimmed = $search !== null ? trim($search) : '';
        if ($trimmed !== '') {
            $needle = strtolower($trimmed);
            $list = array_values(array_filter(
                $list,
                static fn (Project $p): bool => str_contains(strtolower($p->title()), $needle)
                    || str_contains($p->slug()->value(), $needle)
            ));
        }
        if ($status !== null) {
            $list = array_values(array_filter(
                $list,
                static fn (Project $p): bool => $p->status() === $status
            ));
        }

        usort($list, static fn (Project $a, Project $b): int => $a->sortOrder() <=> $b->sortOrder());

        $total = count($list);
        $offset = max(0, ($page - 1) * $perPage);
        $slice = array_slice($list, $offset, $perPage);

        return [
            'total' => $total,
            'items' => array_map(
                static fn (Project $p): array => ProjectPersistenceMapper::toSummary($p),
                $slice
            ),
        ];
    }
}
