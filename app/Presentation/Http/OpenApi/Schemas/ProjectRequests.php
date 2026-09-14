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
    schema: 'CreateProjectRequest',
    type: 'object',
    required: ['title'],
    properties: [
        new SA\Property(property: 'title', type: 'string', minLength: 2, maxLength: 200, example: 'Portfolio 3D'),
        new SA\Property(property: 'slug', type: 'string', nullable: true, example: 'portfolio-3d'),
        new SA\Property(property: 'description', type: 'string', nullable: true),
        new SA\Property(property: 'content', type: 'string', nullable: true),
        new SA\Property(property: 'repository_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'demo_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'thumbnail', type: 'string', nullable: true),
        new SA\Property(property: 'cover', type: 'string', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], nullable: true),
        new SA\Property(property: 'featured', type: 'boolean', nullable: true),
        new SA\Property(property: 'categories', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
        new SA\Property(property: 'technologies', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
        new SA\Property(property: 'tags', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'UpdateProjectRequest',
    type: 'object',
    required: ['title'],
    properties: [
        new SA\Property(property: 'title', type: 'string', minLength: 2, maxLength: 200),
        new SA\Property(property: 'slug', type: 'string', nullable: true),
        new SA\Property(property: 'description', type: 'string', nullable: true),
        new SA\Property(property: 'content', type: 'string', nullable: true),
        new SA\Property(property: 'repository_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'demo_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'thumbnail', type: 'string', nullable: true),
        new SA\Property(property: 'cover', type: 'string', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], nullable: true),
        new SA\Property(property: 'featured', type: 'boolean', nullable: true),
        new SA\Property(property: 'categories', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
        new SA\Property(property: 'technologies', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
        new SA\Property(property: 'tags', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'PatchProjectRequest',
    type: 'object',
    properties: [
        new SA\Property(property: 'title', type: 'string', nullable: true),
        new SA\Property(property: 'slug', type: 'string', nullable: true),
        new SA\Property(property: 'description', type: 'string', nullable: true),
        new SA\Property(property: 'content', type: 'string', nullable: true),
        new SA\Property(property: 'repository_url', type: 'string', nullable: true),
        new SA\Property(property: 'demo_url', type: 'string', nullable: true),
        new SA\Property(property: 'thumbnail', type: 'string', nullable: true),
        new SA\Property(property: 'cover', type: 'string', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], nullable: true),
        new SA\Property(property: 'featured', type: 'boolean', nullable: true),
        new SA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
        new SA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
        new SA\Property(property: 'categories', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
        new SA\Property(property: 'technologies', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
        new SA\Property(property: 'tags', type: 'array', items: new SA\Items(type: 'string', format: 'uuid'), nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'ReorderProjectsRequest',
    type: 'object',
    required: ['items'],
    properties: [
        new SA\Property(
            property: 'items',
            type: 'array',
            items: new SA\Items(
                type: 'object',
                required: ['id', 'sort_order'],
                properties: [
                    new SA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new SA\Property(property: 'sort_order', type: 'integer', minimum: 0),
                ],
            ),
        ),
    ]
)]
#[SA\Schema(
    schema: 'PublishRequest',
    type: 'object',
    properties: [
        new SA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'AddProjectImageRequest',
    type: 'object',
    required: ['image_id'],
    properties: [
        new SA\Property(property: 'image_id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'caption', type: 'string', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'ReorderImagesRequest',
    type: 'object',
    required: ['images'],
    properties: [
        new SA\Property(
            property: 'images',
            type: 'array',
            items: new SA\Items(
                type: 'object',
                required: ['id', 'order'],
                properties: [
                    new SA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new SA\Property(property: 'order', type: 'integer'),
                ],
            ),
        ),
    ]
)]
#[SA\Schema(
    schema: 'SetImageRefRequest',
    type: 'object',
    required: ['image_id'],
    properties: [
        new SA\Property(property: 'image_id', type: 'string', format: 'uuid'),
    ]
)]
#[SA\Schema(
    schema: 'SyncTaxonomyRequest',
    type: 'object',
    properties: [
        new SA\Property(property: 'categories', type: 'array', items: new SA\Items(type: 'string', format: 'uuid')),
        new SA\Property(property: 'technologies', type: 'array', items: new SA\Items(type: 'string', format: 'uuid')),
        new SA\Property(property: 'tags', type: 'array', items: new SA\Items(type: 'string', format: 'uuid')),
    ]
)]
final class ProjectRequests
{
}
