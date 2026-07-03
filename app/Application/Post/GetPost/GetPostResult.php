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

namespace App\Application\Post\GetPost;

final class GetPostResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $projectId,
        public readonly string $title,
        public readonly string $body,
        public readonly string $status,
    ) {
    }
}
