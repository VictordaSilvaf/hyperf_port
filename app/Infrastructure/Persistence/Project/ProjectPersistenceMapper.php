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
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;

final class ProjectPersistenceMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toDomain(array $row): Project
    {
        return Project::restore(
            ProjectId::fromString((string) $row['id']),
            (string) $row['title'],
            ProjectSlug::fromString((string) $row['slug']),
            isset($row['description']) ? (string) $row['description'] : null,
            ProjectStatus::from((string) $row['status']),
            (int) $row['sort_order'],
            isset($row['image_path']) ? (string) $row['image_path'] : null,
            isset($row['owner_id']) ? (string) $row['owner_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toRow(Project $project): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id' => $project->id()->value(),
            'title' => $project->title(),
            'slug' => $project->slug()->value(),
            'description' => $project->description(),
            'status' => $project->status()->value,
            'sort_order' => $project->sortOrder(),
            'image_path' => $project->imagePath(),
            'owner_id' => $project->ownerId(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toSummary(Project $project): array
    {
        return [
            'id' => $project->id()->value(),
            'title' => $project->title(),
            'slug' => $project->slug()->value(),
            'status' => $project->status()->value,
            'sort_order' => $project->sortOrder(),
            'image_path' => $project->imagePath(),
        ];
    }
}
