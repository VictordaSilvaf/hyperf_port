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

use App\Application\Project\ArchiveProject\ArchiveProjectCommand;
use App\Application\Project\ArchiveProject\ArchiveProjectHandler;
use App\Application\Project\CreateProject\CreateProjectCommand;
use App\Application\Project\CreateProject\CreateProjectHandler;
use App\Application\Project\DeleteProject\DeleteProjectCommand;
use App\Application\Project\DeleteProject\DeleteProjectHandler;
use App\Application\Project\GetProject\GetProjectHandler;
use App\Application\Project\GetProject\GetProjectQuery;
use App\Application\Project\ListProjects\ListProjectsHandler;
use App\Application\Project\ListProjects\ListProjectsQuery;
use App\Application\Project\PublishProject\PublishProjectCommand;
use App\Application\Project\PublishProject\PublishProjectHandler;
use App\Application\Project\ReorderProjects\ReorderProjectsCommand;
use App\Application\Project\ReorderProjects\ReorderProjectsHandler;
use App\Application\Project\UpdateProject\UpdateProjectCommand;
use App\Application\Project\UpdateProject\UpdateProjectHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\Requests\Admin\CreateProjectRequest;
use App\Presentation\Http\Requests\Admin\ListProjectsRequest;
use App\Presentation\Http\Requests\Admin\ReorderProjectsRequest;
use App\Presentation\Http\Requests\Admin\UpdateProjectRequest;
use App\Presentation\Http\Resources\Project\ProjectResource;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class AdminProjectController extends AbstractController
{
    #[Inject]
    protected ListProjectsHandler $listProjects;

    #[Inject]
    protected GetProjectHandler $getProject;

    #[Inject]
    protected CreateProjectHandler $createProject;

    #[Inject]
    protected UpdateProjectHandler $updateProject;

    #[Inject]
    protected DeleteProjectHandler $deleteProject;

    #[Inject]
    protected PublishProjectHandler $publishProject;

    #[Inject]
    protected ArchiveProjectHandler $archiveProject;

    #[Inject]
    protected ReorderProjectsHandler $reorderProjects;

    public function index(ListProjectsRequest $request): array
    {
        $data = $request->validated();
        $status = isset($data['status']) ? ProjectStatus::from((string) $data['status']) : null;

        return $this->listProjects->handle(new ListProjectsQuery(
            (int) ($data['page'] ?? 1),
            (int) ($data['per_page'] ?? 15),
            isset($data['search']) ? (string) $data['search'] : null,
            $status,
        ));
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $result = $this->getProject->handle(new GetProjectQuery($id));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ProjectResource::fromResult($result);
    }

    public function store(CreateProjectRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $id = $this->createProject->handle(new CreateProjectCommand(
                (string) $data['title'],
                isset($data['slug']) ? (string) $data['slug'] : null,
                isset($data['description']) ? (string) $data['description'] : null,
                isset($data['image_path']) ? (string) $data['image_path'] : null,
                isset($data['owner_id']) ? (string) $data['owner_id'] : null,
            ));
        } catch (ProjectSlugTakenException) {
            return $this->response->json(['message' => trans('http.project_slug_taken')])->withStatus(409);
        }

        return ['id' => $id, 'message' => trans('http.project_created')];
    }

    public function update(string $id, UpdateProjectRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->updateProject->handle(new UpdateProjectCommand(
                $id,
                (string) $data['title'],
                isset($data['slug']) ? (string) $data['slug'] : null,
                isset($data['description']) ? (string) $data['description'] : null,
                isset($data['image_path']) ? (string) $data['image_path'] : null,
            ));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        } catch (ProjectSlugTakenException) {
            return $this->response->json(['message' => trans('http.project_slug_taken')])->withStatus(409);
        }

        return ['message' => trans('http.project_updated')];
    }

    public function destroy(string $id): array|PsrResponseInterface
    {
        try {
            $this->deleteProject->handle(new DeleteProjectCommand($id));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.project_deleted')];
    }

    public function publish(string $id): array|PsrResponseInterface
    {
        try {
            $this->publishProject->handle(new PublishProjectCommand($id));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.project_published')];
    }

    public function archive(string $id): array|PsrResponseInterface
    {
        try {
            $this->archiveProject->handle(new ArchiveProjectCommand($id));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.project_archived')];
    }

    public function reorder(ReorderProjectsRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->reorderProjects->handle(new ReorderProjectsCommand($data['items']));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ['message' => trans('http.projects_reordered')];
    }
}
