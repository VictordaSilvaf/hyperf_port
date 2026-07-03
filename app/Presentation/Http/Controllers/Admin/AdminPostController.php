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

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Post\CreatePost\CreatePostCommand;
use App\Application\Post\CreatePost\CreatePostHandler;
use App\Application\Post\DeletePost\DeletePostCommand;
use App\Application\Post\DeletePost\DeletePostHandler;
use App\Application\Post\GetPost\GetPostHandler;
use App\Application\Post\GetPost\GetPostQuery;
use App\Application\Post\ListPosts\ListPostsHandler;
use App\Application\Post\ListPosts\ListPostsQuery;
use App\Application\Post\PublishPost\PublishPostCommand;
use App\Application\Post\PublishPost\PublishPostHandler;
use App\Application\Post\UpdatePost\UpdatePostCommand;
use App\Application\Post\UpdatePost\UpdatePostHandler;
use App\Domain\Post\Exception\PostNotFoundException;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\Requests\Admin\CreatePostRequest;
use App\Presentation\Http\Requests\Admin\UpdatePostRequest;
use App\Presentation\Http\Resources\Post\PostResource;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class AdminPostController extends AbstractController
{
    #[Inject]
    protected ListPostsHandler $listPosts;

    #[Inject]
    protected GetPostHandler $getPost;

    #[Inject]
    protected CreatePostHandler $createPost;

    #[Inject]
    protected UpdatePostHandler $updatePost;

    #[Inject]
    protected DeletePostHandler $deletePost;

    #[Inject]
    protected PublishPostHandler $publishPost;

    public function index(string $projectId): array|PsrResponseInterface
    {
        try {
            return $this->listPosts->handle(new ListPostsQuery(
                $projectId,
                (int) $this->request->input('page', 1),
                (int) $this->request->input('per_page', 15),
            ));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $result = $this->getPost->handle(new GetPostQuery($id));
        } catch (PostNotFoundException) {
            return $this->response->json(['message' => trans('http.post_not_found')])->withStatus(404);
        }

        return PostResource::fromResult($result);
    }

    public function store(CreatePostRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $id = $this->createPost->handle(new CreatePostCommand(
                (string) $data['project_id'],
                (string) $data['title'],
                (string) $data['body'],
            ));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ['id' => $id, 'message' => trans('http.post_created')];
    }

    public function update(string $id, UpdatePostRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->updatePost->handle(new UpdatePostCommand(
                $id,
                (string) $data['title'],
                (string) $data['body'],
            ));
        } catch (PostNotFoundException) {
            return $this->response->json(['message' => trans('http.post_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.post_updated')];
    }

    public function destroy(string $id): array|PsrResponseInterface
    {
        try {
            $this->deletePost->handle(new DeletePostCommand($id));
        } catch (PostNotFoundException) {
            return $this->response->json(['message' => trans('http.post_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.post_deleted')];
    }

    public function publish(string $id): array|PsrResponseInterface
    {
        try {
            $this->publishPost->handle(new PublishPostCommand($id));
        } catch (PostNotFoundException) {
            return $this->response->json(['message' => trans('http.post_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.post_published')];
    }
}
