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

use App\Application\Page\GetHomePage\GetHomePageHandler;
use App\Application\Page\GetPageBySlug\GetPageBySlugHandler;
use App\Application\Page\ListPages\ListPagesHandler;
use App\Application\Page\ListPages\ListPagesQuery;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class PageController extends AbstractController
{
    #[Inject]
    protected ListPagesHandler $listPages;

    #[Inject]
    protected GetPageBySlugHandler $getPageBySlug;

    #[Inject]
    protected GetHomePageHandler $getHomePage;

    public function index(): array
    {
        return $this->listPages->handle(new ListPagesQuery(
            page: (int) $this->request->input('page', 1),
            perPage: (int) $this->request->input('per_page', 15),
            publicOnly: true,
        ));
    }

    public function show(string $slug): array|PsrResponseInterface
    {
        try {
            return $this->getPageBySlug->handle($slug);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    public function home(): array|PsrResponseInterface
    {
        try {
            return $this->getHomePage->handle();
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }
}
