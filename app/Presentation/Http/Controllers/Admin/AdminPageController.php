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

use App\Application\Page\ArchivePage\ArchivePageCommand;
use App\Application\Page\ArchivePage\ArchivePageHandler;
use App\Application\Page\CreatePage\CreatePageCommand;
use App\Application\Page\CreatePage\CreatePageHandler;
use App\Application\Page\DeletePage\DeletePageCommand;
use App\Application\Page\DeletePage\DeletePageHandler;
use App\Application\Page\DraftPage\DraftPageCommand;
use App\Application\Page\DraftPage\DraftPageHandler;
use App\Application\Page\DuplicatePage\DuplicatePageCommand;
use App\Application\Page\DuplicatePage\DuplicatePageHandler;
use App\Application\Page\GetPage\GetPageHandler;
use App\Application\Page\GetPage\GetPageQuery;
use App\Application\Page\ListPages\ListPagesHandler;
use App\Application\Page\ListPages\ListPagesQuery;
use App\Application\Page\PatchPage\PatchPageCommand;
use App\Application\Page\PatchPage\PatchPageHandler;
use App\Application\Page\PublishPage\PublishPageCommand;
use App\Application\Page\PublishPage\PublishPageHandler;
use App\Application\Page\ReorderPages\ReorderPagesCommand;
use App\Application\Page\ReorderPages\ReorderPagesHandler;
use App\Application\Page\RestorePage\RestorePageCommand;
use App\Application\Page\RestorePage\RestorePageHandler;
use App\Application\Page\SyncPageBlocks\SyncPageBlocksCommand;
use App\Application\Page\SyncPageBlocks\SyncPageBlocksHandler;
use App\Application\Page\UpdatePage\UpdatePageCommand;
use App\Application\Page\UpdatePage\UpdatePageHandler;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Exception\PageSlugTakenException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\CreatePageRequest;
use App\Presentation\Http\Requests\Admin\ListPagesRequest;
use App\Presentation\Http\Requests\Admin\PatchPageRequest;
use App\Presentation\Http\Requests\Admin\ReorderPagesRequest;
use App\Presentation\Http\Requests\Admin\SyncPageBlocksRequest;
use App\Presentation\Http\Requests\Admin\UpdatePageRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class AdminPageController extends AbstractController
{
    #[Inject]
    protected ListPagesHandler $listPages;

    #[Inject]
    protected GetPageHandler $getPage;

    #[Inject]
    protected CreatePageHandler $createPage;

    #[Inject]
    protected UpdatePageHandler $updatePage;

    #[Inject]
    protected PatchPageHandler $patchPage;

    #[Inject]
    protected PublishPageHandler $publishPage;

    #[Inject]
    protected ArchivePageHandler $archivePage;

    #[Inject]
    protected DraftPageHandler $draftPage;

    #[Inject]
    protected DeletePageHandler $deletePage;

    #[Inject]
    protected RestorePageHandler $restorePage;

    #[Inject]
    protected ReorderPagesHandler $reorderPages;

    #[Inject]
    protected DuplicatePageHandler $duplicatePage;

    #[Inject]
    protected SyncPageBlocksHandler $syncPageBlocks;

    #[SA\Get(path: '/api/v1/admin/pages', summary: 'Listar páginas', description: 'Requires permission: pages.view', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/PageListResponse'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function index(ListPagesRequest $request): array
    {
        $data = $request->validated();

        return $this->listPages->handle(new ListPagesQuery(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 15),
            publicOnly: false,
        ));
    }

    #[SA\Get(path: '/api/v1/admin/pages/{id}', summary: 'Obter página', description: 'Requires permission: pages.view', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\QueryParameter(name: 'with_trashed', required: false, schema: new SA\Schema(type: 'boolean', default: false))]
    #[SA\Response(response: 200, description: 'Página', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $withTrashed = filter_var($this->request->input('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

            return $this->getPage->handle(new GetPageQuery($id, $withTrashed));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Post(path: '/api/v1/admin/pages', summary: 'Criar página', description: 'Requires permission: pages.create', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreatePageRequest'))]
    #[SA\Response(response: 200, description: 'Página criada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function store(CreatePageRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            return $this->createPage->handle(new CreatePageCommand(
                (string) $data['title'],
                $data['slug'] ?? null,
                $data['layout'] ?? null,
                $data['seo'] ?? null,
                (bool) ($data['is_home'] ?? false),
                $data['status'] ?? null,
            ));
        } catch (PageSlugTakenException) {
            return $this->response->json(['message' => trans('http.page_slug_taken')])->withStatus(409);
        }
    }

    #[SA\Put(path: '/api/v1/admin/pages/{id}', summary: 'Actualizar página', description: 'Requires permission: pages.update', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/UpdatePageRequest'))]
    #[SA\Response(response: 200, description: 'Página actualizada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function update(string $id, UpdatePageRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            return $this->updatePage->handle(new UpdatePageCommand(
                $id,
                (string) $data['title'],
                $data['slug'] ?? null,
                (string) ($data['layout'] ?? 'default'),
                $data['seo'] ?? null,
                (bool) ($data['is_home'] ?? false),
                $data['status'] ?? null,
            ));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        } catch (PageSlugTakenException) {
            return $this->response->json(['message' => trans('http.page_slug_taken')])->withStatus(409);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/pages/{id}', summary: 'Patch página', description: 'Requires permission: pages.update', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/PatchPageRequest'))]
    #[SA\Response(response: 200, description: 'Página actualizada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function patch(string $id, PatchPageRequest $request): array|PsrResponseInterface
    {
        try {
            return $this->patchPage->handle(new PatchPageCommand($id, $request->validated()));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        } catch (PageSlugTakenException) {
            return $this->response->json(['message' => trans('http.page_slug_taken')])->withStatus(409);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/pages/{id}/publish', summary: 'Publicar página', description: 'Requires permission: pages.publish', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: false, content: new SA\JsonContent(ref: '#/components/schemas/PublishRequest'))]
    #[SA\Response(response: 200, description: 'Publicada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function publish(string $id): array|PsrResponseInterface
    {
        try {
            $publishedAt = $this->request->input('published_at');

            return $this->publishPage->handle(new PublishPageCommand(
                $id,
                is_string($publishedAt) ? $publishedAt : null,
            ));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/pages/{id}/archive', summary: 'Arquivar página', description: 'Requires permission: pages.publish', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Arquivada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function archive(string $id): array|PsrResponseInterface
    {
        try {
            return $this->archivePage->handle(new ArchivePageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/pages/{id}/draft', summary: 'Passar página a rascunho', description: 'Requires permission: pages.publish', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Rascunho', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function draft(string $id): array|PsrResponseInterface
    {
        try {
            return $this->draftPage->handle(new DraftPageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Delete(path: '/api/v1/admin/pages/{id}', summary: 'Soft-delete página', description: 'Requires permission: pages.delete', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Eliminada', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function destroy(string $id): PsrResponseInterface
    {
        try {
            $this->deletePage->handle(new DeletePageCommand($id, false));

            return $this->response->json(['message' => trans('http.page_deleted')]);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/pages/{id}/restore', summary: 'Restaurar página', description: 'Requires permission: pages.update', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Restaurada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function restore(string $id): array|PsrResponseInterface
    {
        try {
            return $this->restorePage->handle(new RestorePageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Delete(path: '/api/v1/admin/pages/{id}/force', summary: 'Hard-delete página', description: 'Requires permission: pages.delete', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Eliminada permanentemente', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function forceDestroy(string $id): PsrResponseInterface
    {
        try {
            $this->deletePage->handle(new DeletePageCommand($id, true));

            return $this->response->json(['message' => trans('http.page_deleted')]);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Patch(path: '/api/v1/admin/pages/order', summary: 'Reordenar páginas', description: 'Requires permission: pages.update', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/ReorderPagesRequest'))]
    #[SA\Response(response: 200, description: 'Reordenadas', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function reorder(ReorderPagesRequest $request): PsrResponseInterface
    {
        try {
            $this->reorderPages->handle(new ReorderPagesCommand($request->validated()['items']));

            return $this->response->json(['message' => trans('http.pages_reordered')]);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Post(path: '/api/v1/admin/pages/{id}/duplicate', summary: 'Duplicar página', description: 'Requires permission: pages.create', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Duplicada', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function duplicate(string $id): array|PsrResponseInterface
    {
        try {
            return $this->duplicatePage->handle(new DuplicatePageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        } catch (PageSlugTakenException) {
            return $this->response->json(['message' => trans('http.page_slug_taken')])->withStatus(409);
        }
    }

    #[SA\Put(path: '/api/v1/admin/pages/{id}/blocks', summary: 'Sincronizar blocos', description: 'Requires permission: pages.update', security: OpenApiRefs::BEARER, tags: ['Admin Pages'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SyncPageBlocksRequest'))]
    #[SA\Response(response: 200, description: 'Blocos sincronizados', content: new SA\JsonContent(ref: '#/components/schemas/PageDetailEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function syncBlocks(string $id, SyncPageBlocksRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            return $this->syncPageBlocks->handle(new SyncPageBlocksCommand($id, $data['blocks']));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }
}
