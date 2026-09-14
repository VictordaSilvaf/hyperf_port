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
    schema: 'ValidationError',
    type: 'object',
    required: ['message', 'errors'],
    properties: [
        new SA\Property(property: 'message', type: 'string', example: 'Validation failed'),
        new SA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new SA\AdditionalProperties(
                type: 'array',
                items: new SA\Items(type: 'string'),
            ),
            example: ['email' => ['The email field is required.']],
        ),
    ]
)]
final class ValidationError
{
}
