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

namespace App\Infrastructure\Persistence\Post;

use App\Domain\Post\Entity\Post;
use App\Domain\Post\ValueObject\PostId;
use App\Domain\Post\ValueObject\PostStatus;
use App\Domain\Project\ValueObject\ProjectId;

final class PostPersistenceMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toDomain(array $row): Post
    {
        return Post::restore(
            PostId::fromString((string) $row['id']),
            ProjectId::fromString((string) $row['project_id']),
            (string) $row['title'],
            (string) $row['body'],
            PostStatus::from((string) $row['status']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toRow(Post $post): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id' => $post->id()->value(),
            'project_id' => $post->projectId()->value(),
            'title' => $post->title(),
            'body' => $post->body(),
            'status' => $post->status()->value,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
