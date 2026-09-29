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
    schema: 'CreateTaxonomyRequest',
    type: 'object',
    required: ['name'],
    properties: [
        new SA\Property(property: 'name', type: 'string', example: 'React'),
        new SA\Property(property: 'slug', type: 'string', nullable: true, example: 'react'),
    ]
)]
#[SA\Schema(
    schema: 'TaxonomyItemEnvelope',
    type: 'object',
    required: ['data'],
    properties: [
        new SA\Property(property: 'data', ref: '#/components/schemas/TaxonomyItem'),
    ]
)]
final class TaxonomyCreateSchemas
{
}
