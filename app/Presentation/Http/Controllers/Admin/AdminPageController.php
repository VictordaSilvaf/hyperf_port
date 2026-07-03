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
use App\Presentation\Http\Requests\Admin\CreatePageRequest;
use App\Presentation\Http\Requests\Admin\ListPagesRequest;
use App\Presentation\Http\Requests\Admin\PatchPageRequest;
use App\Presentation\Http\Requests\Admin\ReorderPagesRequest;
use App\Presentation\Http\Requests\Admin\SyncPageBlocksRequest;
use App\Presentation\Http\Requests\Admin\UpdatePageRequest;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

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

    public function index(ListPagesRequest $request): array
    {
        $data = $request->validated();

        return $this->listPages->handle(new ListPagesQuery(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 15),
            publicOnly: false,
        ));
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $withTrashed = filter_var($this->request->input('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

            return $this->getPage->handle(new GetPageQuery($id, $withTrashed));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

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

    public function archive(string $id): array|PsrResponseInterface
    {
        try {
            return $this->archivePage->handle(new ArchivePageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    public function draft(string $id): array|PsrResponseInterface
    {
        try {
            return $this->draftPage->handle(new DraftPageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    public function destroy(string $id): PsrResponseInterface
    {
        try {
            $this->deletePage->handle(new DeletePageCommand($id, false));

            return $this->response->json(['message' => trans('http.page_deleted')]);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    public function restore(string $id): array|PsrResponseInterface
    {
        try {
            return $this->restorePage->handle(new RestorePageCommand($id));
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    public function forceDestroy(string $id): PsrResponseInterface
    {
        try {
            $this->deletePage->handle(new DeletePageCommand($id, true));

            return $this->response->json(['message' => trans('http.page_deleted')]);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    public function reorder(ReorderPagesRequest $request): PsrResponseInterface
    {
        try {
            $this->reorderPages->handle(new ReorderPagesCommand($request->validated()['items']));

            return $this->response->json(['message' => trans('http.pages_reordered')]);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

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
