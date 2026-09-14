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
    schema: 'TaxonomyItem',
    type: 'object',
    required: ['id', 'name', 'slug'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid', example: 't1000001-0000-4000-8000-000000000001'),
        new SA\Property(property: 'name', type: 'string', example: 'Laravel'),
        new SA\Property(property: 'slug', type: 'string', example: 'laravel'),
    ]
)]
final class TaxonomyItem
{
}
