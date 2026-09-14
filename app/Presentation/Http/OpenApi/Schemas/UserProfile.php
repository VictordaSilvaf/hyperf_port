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
    schema: 'UserPublic',
    type: 'object',
    required: ['id', 'name', 'email'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid', example: 'u0000001-0000-4000-8000-000000000001'),
        new SA\Property(property: 'name', type: 'string', example: 'Victor Fernandes'),
        new SA\Property(property: 'email', type: 'string', format: 'email', example: 'victor@exemplo.com'),
    ]
)]
#[SA\Schema(
    schema: 'UserProfile',
    type: 'object',
    required: ['id', 'name', 'email', 'roles', 'permissions'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'name', type: 'string'),
        new SA\Property(property: 'email', type: 'string', format: 'email'),
        new SA\Property(property: 'roles', type: 'array', items: new SA\Items(type: 'string')),
        new SA\Property(property: 'permissions', type: 'array', items: new SA\Items(type: 'string')),
        new SA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new SA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'AdminUserList',
    type: 'object',
    required: ['total', 'items'],
    properties: [
        new SA\Property(property: 'total', type: 'integer', example: 10),
        new SA\Property(
            property: 'items',
            type: 'array',
            items: new SA\Items(ref: '#/components/schemas/UserPublic'),
        ),
    ]
)]
final class UserProfile
{
}
