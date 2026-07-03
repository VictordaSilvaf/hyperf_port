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

use App\Domain\Post\Exception\PostNotFoundException;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Post\ValueObject\PostId;

final class GetPostHandler
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    public function handle(GetPostQuery $query): GetPostResult
    {
        $id = PostId::fromString($query->postId);
        $post = $this->posts->findById($id);
        if ($post === null || ($query->publicOnly && ! $post->status()->isPublic())) {
            throw PostNotFoundException::byId($query->postId);
        }

        return new GetPostResult(
            $post->id()->value(),
            $post->projectId()->value(),
            $post->title(),
            $post->body(),
            $post->status()->value,
        );
    }
}
