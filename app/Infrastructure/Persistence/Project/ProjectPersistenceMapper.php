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
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Domain\Upload\ValueObject\UploadId;
use DateTimeImmutable;

final class ProjectPersistenceMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toDomain(array $row): Project
    {
        $publishedAt = null;
        if (! empty($row['published_at'])) {
            $publishedAt = new DateTimeImmutable((string) $row['published_at']);
        }

        return Project::restore([
            'id' => ProjectId::fromString((string) $row['id']),
            'title' => (string) $row['title'],
            'slug' => ProjectSlug::fromString((string) $row['slug']),
            'description' => isset($row['description']) ? (string) $row['description'] : null,
            'content' => isset($row['content']) ? (string) $row['content'] : null,
            'repository_url' => isset($row['repository_url']) ? (string) $row['repository_url'] : null,
            'demo_url' => isset($row['demo_url']) ? (string) $row['demo_url'] : null,
            'thumbnail' => isset($row['thumbnail_path']) ? (string) $row['thumbnail_path'] : null,
            'cover' => isset($row['cover_path']) ? (string) $row['cover_path'] : null,
            'status' => ProjectStatus::from((string) $row['status']),
            'featured' => (bool) ($row['featured'] ?? false),
            'published_at' => $publishedAt,
            'sort_order' => (int) $row['sort_order'],
            'views' => (int) ($row['views'] ?? 0),
            'owner_id' => isset($row['owner_id']) ? (string) $row['owner_id'] : null,
        ]);
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
            'content' => $project->content(),
            'repository_url' => $project->repositoryUrl(),
            'demo_url' => $project->demoUrl(),
            'thumbnail_path' => $project->thumbnailPath(),
            'cover_path' => $project->coverPath(),
            'status' => $project->status()->value,
            'featured' => $project->featured(),
            'published_at' => $project->publishedAt()?->format('Y-m-d H:i:s'),
            'sort_order' => $project->sortOrder(),
            'views' => $project->views(),
            'owner_id' => $project->ownerId(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function imageToDomain(array $row): ProjectImage
    {
        return ProjectImage::restore(
            ProjectImageId::fromString((string) $row['id']),
            ProjectId::fromString((string) $row['project_id']),
            UploadId::fromString((string) $row['upload_id']),
            isset($row['caption']) ? (string) $row['caption'] : null,
            (int) $row['sort_order'],
        );
    }
}
