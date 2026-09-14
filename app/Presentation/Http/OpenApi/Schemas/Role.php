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
    schema: 'Role',
    type: 'object',
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'name', type: 'string', example: 'Administrator'),
        new SA\Property(property: 'slug', type: 'string', example: 'admin'),
    ]
)]
#[SA\Schema(
    schema: 'Permission',
    type: 'object',
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'slug', type: 'string', example: 'projects.view'),
        new SA\Property(property: 'description', type: 'string', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'RoleList',
    type: 'object',
    properties: [
        new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/Role')),
    ]
)]
#[SA\Schema(
    schema: 'PermissionList',
    type: 'object',
    properties: [
        new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/Permission')),
    ]
)]
#[SA\Schema(
    schema: 'UploadResult',
    type: 'object',
    required: ['id', 'url', 'path'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'url', type: 'string', format: 'uri'),
        new SA\Property(property: 'path', type: 'string', example: 'uploads/2026/07/abc123.png'),
        new SA\Property(property: 'processing_status', type: 'string', nullable: true),
        new SA\Property(property: 'display_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'thumbnail_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'width', type: 'integer', nullable: true),
        new SA\Property(property: 'height', type: 'integer', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'IndexResponse',
    type: 'object',
    properties: [
        new SA\Property(property: 'method', type: 'string', example: 'GET'),
        new SA\Property(property: 'message', type: 'string', example: 'Hello Hyperf.'),
    ]
)]
final class Role
{
}
