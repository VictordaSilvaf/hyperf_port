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

namespace App\Presentation\Http\Controllers\Public;

use App\Application\Post\GetPost\GetPostHandler;
use App\Application\Post\GetPost\GetPostQuery;
use App\Application\Post\ListPosts\ListPostsHandler;
use App\Application\Post\ListPosts\ListPostsQuery;
use App\Domain\Post\Exception\PostNotFoundException;
use App\Domain\Post\ValueObject\PostStatus;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\Resources\Post\PostResource;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class PostController extends AbstractController
{
    #[Inject]
    protected ListPostsHandler $listPosts;

    #[Inject]
    protected GetPostHandler $getPost;

    public function index(string $projectId): array|PsrResponseInterface
    {
        try {
            return $this->listPosts->handle(new ListPostsQuery(
                $projectId,
                (int) $this->request->input('page', 1),
                (int) $this->request->input('per_page', 15),
                PostStatus::Published,
            ));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $result = $this->getPost->handle(new GetPostQuery($id, true));
        } catch (PostNotFoundException) {
            return $this->response->json(['message' => trans('http.post_not_found')])->withStatus(404);
        }

        return PostResource::fromResult($result);
    }
}
