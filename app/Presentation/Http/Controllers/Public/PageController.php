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
use App\Presentation\Http\OpenApi\OpenApiRefs;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class PageController extends AbstractController
{
    #[Inject]
    protected ListPagesHandler $listPages;

    #[Inject]
    protected GetPageBySlugHandler $getPageBySlug;

    #[Inject]
    protected GetHomePageHandler $getHomePage;

    #[SA\Get(path: '/api/v1/pages', summary: 'Listar páginas públicas', tags: ['Pages (Public)'])]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/PageListResponse'))]
    public function index(): array
    {
        return $this->listPages->handle(new ListPagesQuery(
            page: (int) $this->request->input('page', 1),
            perPage: (int) $this->request->input('per_page', 15),
            publicOnly: true,
        ));
    }

    #[SA\Get(path: '/api/v1/pages/{slug}', summary: 'Página por slug', tags: ['Pages (Public)'])]
    #[SA\PathParameter(name: 'slug', required: true, schema: new SA\Schema(type: 'string', example: 'inicio'))]
    #[SA\Response(response: 200, description: 'Página', content: new SA\JsonContent(ref: '#/components/schemas/PageDetail'))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $slug): array|PsrResponseInterface
    {
        try {
            return $this->getPageBySlug->handle($slug);
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }

    #[SA\Get(path: '/api/v1/pages/home', summary: 'Página home', tags: ['Pages (Public)'])]
    #[SA\Response(response: 200, description: 'Home', content: new SA\JsonContent(ref: '#/components/schemas/PageDetail'))]
    #[SA\Response(response: 404, description: 'Não definida', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function home(): array|PsrResponseInterface
    {
        try {
            return $this->getHomePage->handle();
        } catch (PageNotFoundException) {
            return $this->response->json(['message' => trans('http.page_not_found')])->withStatus(404);
        }
    }
}
