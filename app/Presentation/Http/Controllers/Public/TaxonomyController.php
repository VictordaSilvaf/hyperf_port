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
use App\Application\Tag\ListTags\ListTagsHandler;
use App\Application\Technology\ListTechnologies\ListTechnologiesHandler;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;

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
