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

namespace App\Application\Post\DeletePost;

use App\Domain\Post\Exception\PostNotFoundException;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Post\ValueObject\PostId;

final class DeletePostHandler
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    public function handle(DeletePostCommand $command): void
    {
        $id = PostId::fromString($command->postId);
        if ($this->posts->findById($id) === null) {
            throw PostNotFoundException::byId($command->postId);
        }

        $this->posts->delete($id);
    }
}
