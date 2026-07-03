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

namespace App\Application\Post\ListPosts;

use App\Domain\Post\ValueObject\PostStatus;

final class ListPostsQuery
{
    public function __construct(
        public readonly string $projectId,
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?PostStatus $status = null,
    ) {
    }
}
