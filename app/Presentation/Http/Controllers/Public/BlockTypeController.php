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

use App\Application\Page\ListBlockTypes\ListBlockTypesHandler;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;

#[SA\HyperfServer('openapi')]
final class BlockTypeController extends AbstractController
{
    #[Inject]
    protected ListBlockTypesHandler $listBlockTypes;

    #[SA\Get(path: '/api/v1/block-types', summary: 'Tipos de bloco disponíveis', tags: ['Pages (Public)'])]
    #[SA\Response(
        response: 200,
        description: 'Lista de block types',
        content: new SA\JsonContent(
            type: 'object',
            properties: [
                new SA\Property(
                    property: 'data',
                    type: 'array',
                    items: new SA\Items(ref: '#/components/schemas/BlockType'),
                ),
            ],
        ),
    )]
    public function index(): array
    {
        return $this->listBlockTypes->handle();
    }
}
