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
    schema: 'CreateAdminUserRequest',
    type: 'object',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new SA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 100),
        new SA\Property(property: 'email', type: 'string', format: 'email'),
        new SA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new SA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
#[SA\Schema(
    schema: 'UpdateAdminUserRequest',
    type: 'object',
    required: ['name', 'email'],
    properties: [
        new SA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 100),
        new SA\Property(property: 'email', type: 'string', format: 'email'),
    ]
)]
#[SA\Schema(
    schema: 'CreateRoleRequest',
    type: 'object',
    required: ['name', 'slug'],
    properties: [
        new SA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 120, example: 'Editor'),
        new SA\Property(property: 'slug', type: 'string', pattern: '^[a-z0-9_-]{1,64}$', example: 'editor'),
    ]
)]
#[SA\Schema(
    schema: 'SyncRolePermissionsRequest',
    type: 'object',
    required: ['permission_slugs'],
    properties: [
        new SA\Property(
            property: 'permission_slugs',
            type: 'array',
            items: new SA\Items(type: 'string'),
            example: ['projects.view', 'projects.update'],
        ),
    ]
)]
#[SA\Schema(
    schema: 'SyncUserRolesRequest',
    type: 'object',
    required: ['role_slugs'],
    properties: [
        new SA\Property(
            property: 'role_slugs',
            type: 'array',
            items: new SA\Items(type: 'string'),
            example: ['admin', 'manager'],
        ),
    ]
)]
#[SA\Schema(
    schema: 'UpdateContactMessageRequest',
    type: 'object',
    required: ['status'],
    properties: [
        new SA\Property(property: 'status', type: 'string', enum: ['read', 'archived']),
    ]
)]
#[SA\Schema(
    schema: 'UpdateSiteSettingsRequest',
    type: 'object',
    properties: [
        new SA\Property(property: 'nav', type: 'array', items: new SA\Items(type: 'object'), nullable: true),
        new SA\Property(property: 'footer', type: 'object', nullable: true),
        new SA\Property(property: 'social', type: 'object', nullable: true),
        new SA\Property(property: 'branding', type: 'object', nullable: true),
        new SA\Property(property: 'seo', type: 'object', nullable: true),
        new SA\Property(property: 'contact', type: 'object', nullable: true),
    ]
)]
final class AdminUserRequests
{
}
