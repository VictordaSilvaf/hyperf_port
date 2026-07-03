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

use App\Application\Project\ArchiveProject\ArchiveProjectHandler;
use App\Application\Project\CreateProject\CreateProjectCommand;
use App\Application\Project\CreateProject\CreateProjectHandler;
use App\Application\Project\DeleteProject\DeleteProjectHandler;
use App\Application\Project\DraftProject\DraftProjectHandler;
use App\Application\Project\DuplicateProject\DuplicateProjectHandler;
use App\Application\Project\GetProject\GetProjectHandler;
use App\Application\Project\GetProjectStatistics\GetProjectStatisticsHandler;
use App\Application\Project\ListProjects\ListProjectsHandler;
use App\Application\Project\ManageProjectImages\AddProjectImageHandler;
use App\Application\Project\ManageProjectImages\RemoveProjectImageHandler;
use App\Application\Project\ManageProjectImages\ReorderProjectImagesHandler;
use App\Application\Project\ManageProjectImages\SetProjectThumbnailHandler;
use App\Application\Project\PatchProject\PatchProjectCommand;
use App\Application\Project\PatchProject\PatchProjectHandler;
use App\Application\Project\PublishProject\PublishProjectHandler;
use App\Application\Project\ReorderProjects\ReorderProjectsHandler;
use App\Application\Project\RestoreProject\RestoreProjectHandler;
use App\Application\Project\SyncProjectTaxonomies\SyncProjectCategoriesHandler;
use App\Application\Project\SyncProjectTaxonomies\SyncProjectTagsHandler;
use App\Application\Project\SyncProjectTaxonomies\SyncProjectTechnologiesHandler;
use App\Application\Project\UpdateProject\UpdateProjectCommand;
use App\Application\Project\UpdateProject\UpdateProjectHandler;
use App\Application\Upload\StoreUpload\StoreUploadCommand;
use App\Application\Upload\StoreUpload\StoreUploadHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\Requests\Admin\CreateProjectRequest;
use App\Presentation\Http\Requests\Admin\PatchProjectRequest;
use App\Presentation\Http\Requests\Admin\UpdateProjectRequest;
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
    protected PatchProjectHandler $patchProject;

    #[Inject]
    protected PublishProjectHandler $publishProject;

    #[Inject]
    protected ArchiveProjectHandler $archiveProject;

    #[Inject]
    protected DraftProjectHandler $draftProject;

    #[Inject]
    protected DeleteProjectHandler $deleteProject;

    #[Inject]
    protected RestoreProjectHandler $restoreProject;

    #[Inject]
    protected ReorderProjectsHandler $reorderProjects;

    #[Inject]
    protected DuplicateProjectHandler $duplicateProject;

    #[Inject]
    protected GetProjectStatisticsHandler $statistics;

    #[Inject]
    protected AddProjectImageHandler $addImage;

    #[Inject]
    protected RemoveProjectImageHandler $removeImage;

    #[Inject]
    protected ReorderProjectImagesHandler $reorderImages;

    #[Inject]
    protected SetProjectThumbnailHandler $setThumbnail;

    #[Inject]
    protected SyncProjectCategoriesHandler $syncCategories;

    #[Inject]
    protected SyncProjectTechnologiesHandler $syncTechnologies;

    #[Inject]
    protected SyncProjectTagsHandler $syncTags;

    public function index(): array
    {
        return $this->listProjects->fromQueryParams($this->request->all(), false);
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            return $this->getProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function store(CreateProjectRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            return $this->createProject->handle(new CreateProjectCommand(
                (string) $data['title'],
                $data['slug'] ?? null,
                $data['description'] ?? null,
                $data['content'] ?? null,
                $data['repository_url'] ?? null,
                $data['demo_url'] ?? null,
                $data['thumbnail'] ?? null,
                $data['cover'] ?? null,
                $data['status'] ?? null,
                (bool) ($data['featured'] ?? false),
                array_map('strval', $data['categories'] ?? []),
                array_map('strval', $data['technologies'] ?? []),
                array_map('strval', $data['tags'] ?? []),
            ));
        } catch (ProjectSlugTakenException) {
            return $this->response->json(['message' => trans('http.project_slug_taken')])->withStatus(409);
        }
    }

    public function update(string $id, UpdateProjectRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            return $this->updateProject->handle(new UpdateProjectCommand(
                $id,
                (string) $data['title'],
                $data['slug'] ?? null,
                $data['description'] ?? null,
                $data['content'] ?? null,
                $data['repository_url'] ?? null,
                $data['demo_url'] ?? null,
                $data['thumbnail'] ?? null,
                $data['cover'] ?? null,
                $data['status'] ?? null,
                (bool) ($data['featured'] ?? false),
                array_map('strval', $data['categories'] ?? []),
                array_map('strval', $data['technologies'] ?? []),
                array_map('strval', $data['tags'] ?? []),
            ));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        } catch (ProjectSlugTakenException) {
            return $this->response->json(['message' => trans('http.project_slug_taken')])->withStatus(409);
        }
    }

    public function patch(string $id, PatchProjectRequest $request): array|PsrResponseInterface
    {
        try {
            return $this->patchProject->handle(new PatchProjectCommand($id, $request->validated()));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        } catch (ProjectSlugTakenException) {
            return $this->response->json(['message' => trans('http.project_slug_taken')])->withStatus(409);
        }
    }

    public function publish(string $id): array|PsrResponseInterface
    {
        try {
            $publishedAt = $this->request->input('published_at');
            return $this->publishProject->handle($id, is_string($publishedAt) ? $publishedAt : null);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function archive(string $id): array|PsrResponseInterface
    {
        try {
            return $this->archiveProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function draft(string $id): array|PsrResponseInterface
    {
        try {
            return $this->draftProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function destroy(string $id): PsrResponseInterface
    {
        try {
            $this->deleteProject->handle($id, false);
            return $this->response->json(['message' => trans('http.project_deleted')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function restore(string $id): array|PsrResponseInterface
    {
        try {
            return $this->restoreProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function forceDestroy(string $id): PsrResponseInterface
    {
        try {
            $this->deleteProject->handle($id, true);
            return $this->response->json(['message' => trans('http.project_deleted')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function reorder(): PsrResponseInterface
    {
        $projects = $this->request->input('projects', []);
        if (! is_array($projects)) {
            return $this->response->json(['message' => trans('http.validation_failed')])->withStatus(422);
        }
        try {
            $this->reorderProjects->handle($projects);
            return $this->response->json(['message' => trans('http.projects_reordered')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function duplicate(string $id): array|PsrResponseInterface
    {
        try {
            return $this->duplicateProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function stats(): array
    {
        return $this->statistics->handle();
    }

    public function addImage(string $id): array|PsrResponseInterface
    {
        try {
            return $this->addImage->handle(
                $id,
                (string) $this->request->input('image_id'),
                $this->request->input('caption') !== null ? (string) $this->request->input('caption') : null,
            );
        } catch (ProjectNotFoundException $e) {
            return $this->response->json(['message' => $e->getMessage()])->withStatus(404);
        }
    }

    public function removeImage(string $id, string $imageId): PsrResponseInterface
    {
        try {
            $this->removeImage->handle($id, $imageId);
            return $this->response->json(['message' => trans('http.project_updated')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function reorderImages(string $id): PsrResponseInterface
    {
        $images = $this->request->input('images', []);
        if (! is_array($images)) {
            return $this->response->json(['message' => trans('http.validation_failed')])->withStatus(422);
        }
        try {
            $this->reorderImages->handle($id, $images);
            return $this->response->json(['message' => trans('http.projects_reordered')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function setThumbnail(string $id): PsrResponseInterface
    {
        try {
            $this->setThumbnail->handle($id, (string) $this->request->input('image_id'));
            return $this->response->json(['message' => trans('http.project_updated')]);
        } catch (ProjectNotFoundException $e) {
            return $this->response->json(['message' => $e->getMessage()])->withStatus(404);
        }
    }

    public function setCover(string $id): PsrResponseInterface
    {
        try {
            $this->setThumbnail->setCover($id, (string) $this->request->input('image_id'));
            return $this->response->json(['message' => trans('http.project_updated')]);
        } catch (ProjectNotFoundException $e) {
            return $this->response->json(['message' => $e->getMessage()])->withStatus(404);
        }
    }

    public function syncCategories(string $id): array|PsrResponseInterface
    {
        try {
            return $this->syncCategories->handle($id, array_map('strval', (array) $this->request->input('categories', [])));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function syncTechnologies(string $id): array|PsrResponseInterface
    {
        try {
            return $this->syncTechnologies->handle($id, array_map('strval', (array) $this->request->input('technologies', [])));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function syncTags(string $id): array|PsrResponseInterface
    {
        try {
            return $this->syncTags->handle($id, array_map('strval', (array) $this->request->input('tags', [])));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }
}

final class AdminUploadController extends AbstractController
{
    #[Inject]
    protected StoreUploadHandler $storeUpload;

    public function store(): array|PsrResponseInterface
    {
        $file = $this->request->file('file');
        if ($file === null) {
            return $this->response->json(['message' => trans('http.validation_failed')])->withStatus(422);
        }

        $stream = $file->getStream();
        $contents = (string) $stream->getContents();

        return $this->storeUpload->handle(new StoreUploadCommand(
            $contents,
            (string) $file->getClientFilename(),
            (string) ($file->getClientMediaType() ?? 'application/octet-stream'),
        ));
    }
}
