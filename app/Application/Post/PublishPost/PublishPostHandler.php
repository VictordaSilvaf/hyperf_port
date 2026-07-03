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

namespace App\Application\Post\PublishPost;

use App\Domain\Post\Exception\PostNotFoundException;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Post\ValueObject\PostId;

final class PublishPostHandler
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    public function handle(PublishPostCommand $command): void
    {
        $id = PostId::fromString($command->postId);
        $post = $this->posts->findById($id);
        if ($post === null) {
            throw PostNotFoundException::byId($command->postId);
        }

        $this->posts->save($post->publish());
    }
}
