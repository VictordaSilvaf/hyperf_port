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

namespace App\Presentation\Http\OpenApi\Schemas;

use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(
    schema: 'PaginatedMeta',
    type: 'object',
    required: ['total', 'page', 'per_page'],
    properties: [
        new SA\Property(property: 'total', type: 'integer', example: 42),
        new SA\Property(property: 'page', type: 'integer', example: 1),
        new SA\Property(property: 'per_page', type: 'integer', example: 15),
    ]
)]
final class PaginatedMeta
{
}
