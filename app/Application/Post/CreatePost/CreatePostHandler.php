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

namespace App\Application\Post\CreatePost;

use App\Domain\Post\Entity\Post;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class CreatePostHandler
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(CreatePostCommand $command): string
    {
        $projectId = ProjectId::fromString($command->projectId);
        if ($this->projects->findById($projectId) === null) {
            throw ProjectNotFoundException::byId($command->projectId);
        }

        $post = Post::create($projectId, $command->title, $command->body);
        $this->posts->save($post);

        return $post->id()->value();
    }
}
