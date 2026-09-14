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
    schema: 'AuthTokenResponse',
    type: 'object',
    required: ['access_token', 'token_type', 'roles', 'permissions'],
    properties: [
        new SA\Property(property: 'access_token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'),
        new SA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new SA\Property(property: 'roles', type: 'array', items: new SA\Items(type: 'string'), example: ['admin']),
        new SA\Property(
            property: 'permissions',
            type: 'array',
            items: new SA\Items(type: 'string'),
            example: ['projects.view', 'projects.create'],
        ),
        new SA\Property(property: 'id', type: 'string', format: 'uuid', description: 'Presente no registo'),
        new SA\Property(property: 'message', type: 'string', description: 'Presente no registo'),
    ]
)]
final class AuthTokenResponse
{
}
