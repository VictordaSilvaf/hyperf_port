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
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\CreateProjectRequest;
use App\Presentation\Http\Requests\Admin\PatchProjectRequest;
use App\Presentation\Http\Requests\Admin\UpdateProjectRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
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

    #[SA\Get(path: '/api/v1/admin/projects', summary: 'Listar projetos', description: 'Requires permission: projects.view', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\QueryParameter(name: 'search', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'status', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'featured', required: false, schema: new SA\Schema(type: 'boolean'))]
    #[SA\QueryParameter(name: 'category', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'technology', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'tag', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'sort', required: false, schema: new SA\Schema(type: 'string', default: 'sort_order'))]
    #[SA\QueryParameter(name: 'direction', required: false, schema: new SA\Schema(type: 'string', default: 'asc'))]
    #[SA\QueryParameter(name: 'with_trashed', required: false, schema: new SA\Schema(type: 'boolean', default: false))]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/ProjectListResponse'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function index(): array
    {
        return $this->listProjects->fromQueryParams($this->request->all(), false);
    }

    #[SA\Get(path: '/api/v1/admin/projects/{id}', summary: 'Obter projeto', description: 'Requires permission: projects.view', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Projeto', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $id): array|PsrResponseInterface
    {
        try {
            return $this->getProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Post(path: '/api/v1/admin/projects', summary: 'Criar projeto', description: 'Requires permission: projects.create', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreateProjectRequest'))]
    #[SA\Response(response: 200, description: 'Projeto criado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Put(path: '/api/v1/admin/projects/{id}', summary: 'Actualizar projeto', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/UpdateProjectRequest'))]
    #[SA\Response(response: 200, description: 'Projeto actualizado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Patch(path: '/api/v1/admin/projects/{id}', summary: 'Patch projeto', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/PatchProjectRequest'))]
    #[SA\Response(response: 200, description: 'Projeto actualizado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/publish', summary: 'Publicar projeto', description: 'Requires permission: projects.publish', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: false, content: new SA\JsonContent(ref: '#/components/schemas/PublishRequest'))]
    #[SA\Response(response: 200, description: 'Publicado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function publish(string $id): array|PsrResponseInterface
    {
        try {
            $publishedAt = $this->request->input('published_at');
            return $this->publishProject->handle($id, is_string($publishedAt) ? $publishedAt : null);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/archive', summary: 'Arquivar projeto', description: 'Requires permission: projects.publish', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Arquivado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function archive(string $id): array|PsrResponseInterface
    {
        try {
            return $this->archiveProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/draft', summary: 'Passar projeto a rascunho', description: 'Requires permission: projects.publish', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Rascunho', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function draft(string $id): array|PsrResponseInterface
    {
        try {
            return $this->draftProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Delete(path: '/api/v1/admin/projects/{id}', summary: 'Soft-delete projeto', description: 'Requires permission: projects.delete', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Eliminado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function destroy(string $id): PsrResponseInterface
    {
        try {
            $this->deleteProject->handle($id, false);
            return $this->response->json(['message' => trans('http.project_deleted')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/restore', summary: 'Restaurar projeto', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Restaurado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function restore(string $id): array|PsrResponseInterface
    {
        try {
            return $this->restoreProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Delete(path: '/api/v1/admin/projects/{id}/force', summary: 'Hard-delete projeto', description: 'Requires permission: projects.delete', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Eliminado permanentemente', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function forceDestroy(string $id): PsrResponseInterface
    {
        try {
            $this->deleteProject->handle($id, true);
            return $this->response->json(['message' => trans('http.project_deleted')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/projects/order', summary: 'Reordenar projetos', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/ReorderProjectsRequest'))]
    #[SA\Response(response: 200, description: 'Reordenados', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Post(path: '/api/v1/admin/projects/{id}/duplicate', summary: 'Duplicar projeto', description: 'Requires permission: projects.create', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Duplicado', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function duplicate(string $id): array|PsrResponseInterface
    {
        try {
            return $this->duplicateProject->handle($id);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Get(path: '/api/v1/admin/projects/statistics', summary: 'Estatísticas de projetos', description: 'Requires permission: projects.view', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\Response(response: 200, description: 'Estatísticas', content: new SA\JsonContent(ref: '#/components/schemas/ProjectStatistics'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function stats(): array
    {
        return $this->statistics->handle();
    }

    #[SA\Post(path: '/api/v1/admin/projects/{id}/images', summary: 'Adicionar imagem ao projeto', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/AddProjectImageRequest'))]
    #[SA\Response(response: 200, description: 'Imagem adicionada', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Delete(path: '/api/v1/admin/projects/{id}/images/{imageId}', summary: 'Remover imagem do projeto', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\PathParameter(name: 'imageId', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Imagem removida', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function removeImage(string $id, string $imageId): PsrResponseInterface
    {
        try {
            $this->removeImage->handle($id, $imageId);
            return $this->response->json(['message' => trans('http.project_updated')]);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/images/order', summary: 'Reordenar imagens', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/ReorderImagesRequest'))]
    #[SA\Response(response: 200, description: 'Reordenadas', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/thumbnail', summary: 'Definir thumbnail', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SetImageRefRequest'))]
    #[SA\Response(response: 200, description: 'Actualizado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function setThumbnail(string $id): PsrResponseInterface
    {
        try {
            $this->setThumbnail->handle($id, (string) $this->request->input('image_id'));
            return $this->response->json(['message' => trans('http.project_updated')]);
        } catch (ProjectNotFoundException $e) {
            return $this->response->json(['message' => $e->getMessage()])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/projects/{id}/cover', summary: 'Definir cover', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SetImageRefRequest'))]
    #[SA\Response(response: 200, description: 'Actualizado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function setCover(string $id): PsrResponseInterface
    {
        try {
            $this->setThumbnail->setCover($id, (string) $this->request->input('image_id'));
            return $this->response->json(['message' => trans('http.project_updated')]);
        } catch (ProjectNotFoundException $e) {
            return $this->response->json(['message' => $e->getMessage()])->withStatus(404);
        }
    }

    #[SA\Put(path: '/api/v1/admin/projects/{id}/categories', summary: 'Sincronizar categorias', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SyncTaxonomyRequest'))]
    #[SA\Response(response: 200, description: 'Categorias sincronizadas', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function syncCategories(string $id): array|PsrResponseInterface
    {
        try {
            return $this->syncCategories->handle($id, array_map('strval', (array) $this->request->input('categories', [])));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Put(path: '/api/v1/admin/projects/{id}/technologies', summary: 'Sincronizar tecnologias', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SyncTaxonomyRequest'))]
    #[SA\Response(response: 200, description: 'Tecnologias sincronizadas', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function syncTechnologies(string $id): array|PsrResponseInterface
    {
        try {
            return $this->syncTechnologies->handle($id, array_map('strval', (array) $this->request->input('technologies', [])));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Put(path: '/api/v1/admin/projects/{id}/tags', summary: 'Sincronizar tags', description: 'Requires permission: projects.update', security: OpenApiRefs::BEARER, tags: ['Admin Projects'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SyncTaxonomyRequest'))]
    #[SA\Response(response: 200, description: 'Tags sincronizadas', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function syncTags(string $id): array|PsrResponseInterface
    {
        try {
            return $this->syncTags->handle($id, array_map('strval', (array) $this->request->input('tags', [])));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }
}

#[SA\HyperfServer('openapi')]
final class AdminUploadController extends AbstractController
{
    #[Inject]
    protected StoreUploadHandler $storeUpload;

    #[SA\Post(path: '/api/v1/admin/uploads', summary: 'Upload de ficheiro', description: 'Requires permission: uploads.create', security: OpenApiRefs::BEARER, tags: ['Admin Uploads'])]
    #[SA\RequestBody(content: [
        new SA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new SA\Schema(
                required: ['file'],
                properties: [new SA\Property(property: 'file', type: 'string', format: 'binary')],
            ),
        ),
    ])]
    #[SA\Response(response: 200, description: 'Upload OK', content: new SA\JsonContent(ref: '#/components/schemas/UploadResult'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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
