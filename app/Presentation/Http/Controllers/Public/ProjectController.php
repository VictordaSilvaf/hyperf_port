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

use App\Application\Category\ListCategories\ListCategoriesHandler;
use App\Application\Project\GetProjectBySlug\GetProjectBySlugHandler;
use App\Application\Project\GetRelatedProjects\GetRelatedProjectsHandler;
use App\Application\Project\ListProjects\ListProjectsHandler;
use App\Application\Project\SearchProjects\SearchProjectsHandler;
use App\Application\Tag\ListTags\ListTagsHandler;
use App\Application\Technology\ListTechnologies\ListTechnologiesHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Job\FlushProjectViewsJob;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class ProjectController extends AbstractController
{
    #[Inject]
    protected ListProjectsHandler $listProjects;

    #[Inject]
    protected GetProjectBySlugHandler $getProjectBySlug;

    #[Inject]
    protected GetRelatedProjectsHandler $related;

    #[Inject]
    protected SearchProjectsHandler $search;

    #[Inject]
    protected DriverFactory $queue;

    #[SA\Get(path: '/api/v1/projects', summary: 'Listar projetos publicados', tags: ['Portfolio (Public)'])]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\QueryParameter(name: 'search', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'category', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'technology', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\QueryParameter(name: 'tag', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/ProjectListResponse'))]
    public function index(): array
    {
        return $this->listProjects->fromQueryParams($this->request->all(), true);
    }

    #[SA\Get(path: '/api/v1/projects/{slug}', summary: 'Projeto por slug', tags: ['Portfolio (Public)'])]
    #[SA\PathParameter(name: 'slug', required: true, schema: new SA\Schema(type: 'string', example: 'portfolio-3d'))]
    #[SA\Response(response: 200, description: 'Projeto', content: new SA\JsonContent(ref: '#/components/schemas/ProjectDetail'))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $slug): array|PsrResponseInterface
    {
        try {
            $result = $this->getProjectBySlug->handle($slug, true);
            $this->queue->get('default')->push(new FlushProjectViewsJob());

            return $result;
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Get(path: '/api/v1/projects/{slug}/related', summary: 'Projetos relacionados', tags: ['Portfolio (Public)'])]
    #[SA\PathParameter(name: 'slug', required: true, schema: new SA\Schema(type: 'string'))]
    #[SA\Response(
        response: 200,
        description: 'Relacionados',
        content: new SA\JsonContent(
            type: 'object',
            properties: [
                new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/ProjectSummary')),
            ],
        ),
    )]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function related(string $slug): array|PsrResponseInterface
    {
        try {
            return $this->related->handle($slug);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    #[SA\Get(path: '/api/v1/search', summary: 'Pesquisar projetos', tags: ['Portfolio (Public)'])]
    #[SA\QueryParameter(name: 'q', required: false, schema: new SA\Schema(type: 'string', example: 'portfolio'))]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\Response(response: 200, description: 'Resultados', content: new SA\JsonContent(ref: '#/components/schemas/ProjectListResponse'))]
    public function search(): array
    {
        return $this->search->handle(
            (string) $this->request->input('q', ''),
            (int) $this->request->input('page', 1),
            (int) $this->request->input('per_page', 15),
        );
    }
}

#[SA\HyperfServer('openapi')]
final class TaxonomyController extends AbstractController
{
    #[Inject]
    protected ListCategoriesHandler $categories;

    #[Inject]
    protected ListTechnologiesHandler $technologies;

    #[Inject]
    protected ListTagsHandler $tags;

    #[SA\Get(path: '/api/v1/categories', summary: 'Listar categorias', tags: ['Portfolio (Public)'])]
    #[SA\Response(
        response: 200,
        description: 'Categorias',
        content: new SA\JsonContent(
            type: 'object',
            properties: [
                new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/TaxonomyItem')),
            ],
        ),
    )]
    public function categories(): array
    {
        return ['data' => $this->categories->handle()];
    }

    #[SA\Get(path: '/api/v1/technologies', summary: 'Listar tecnologias', tags: ['Portfolio (Public)'])]
    #[SA\Response(
        response: 200,
        description: 'Tecnologias',
        content: new SA\JsonContent(
            type: 'object',
            properties: [
                new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/TaxonomyItem')),
            ],
        ),
    )]
    public function technologies(): array
    {
        return ['data' => $this->technologies->handle()];
    }

    #[SA\Get(path: '/api/v1/tags', summary: 'Listar tags', tags: ['Portfolio (Public)'])]
    #[SA\Response(
        response: 200,
        description: 'Tags',
        content: new SA\JsonContent(
            type: 'object',
            properties: [
                new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/TaxonomyItem')),
            ],
        ),
    )]
    public function tags(): array
    {
        return ['data' => $this->tags->handle()];
    }
}
