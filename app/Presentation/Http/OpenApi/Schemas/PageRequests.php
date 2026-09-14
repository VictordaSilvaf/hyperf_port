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
    schema: 'CreatePageRequest',
    type: 'object',
    required: ['title'],
    properties: [
        new SA\Property(property: 'title', type: 'string', minLength: 2, maxLength: 200, example: 'Início'),
        new SA\Property(property: 'slug', type: 'string', nullable: true, example: 'inicio'),
        new SA\Property(property: 'layout', type: 'string', enum: ['default', 'full-width', 'landing'], nullable: true),
        new SA\Property(property: 'is_home', type: 'boolean', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], nullable: true),
        new SA\Property(property: 'seo', ref: '#/components/schemas/PageSeo', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'UpdatePageRequest',
    type: 'object',
    required: ['title'],
    properties: [
        new SA\Property(property: 'title', type: 'string', minLength: 2, maxLength: 200),
        new SA\Property(property: 'slug', type: 'string', nullable: true),
        new SA\Property(property: 'layout', type: 'string', enum: ['default', 'full-width', 'landing'], nullable: true),
        new SA\Property(property: 'is_home', type: 'boolean', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], nullable: true),
        new SA\Property(property: 'seo', ref: '#/components/schemas/PageSeo', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'PatchPageRequest',
    type: 'object',
    properties: [
        new SA\Property(property: 'title', type: 'string', nullable: true),
        new SA\Property(property: 'slug', type: 'string', nullable: true),
        new SA\Property(property: 'layout', type: 'string', enum: ['default', 'full-width', 'landing'], nullable: true),
        new SA\Property(property: 'is_home', type: 'boolean', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived'], nullable: true),
        new SA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
        new SA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
        new SA\Property(property: 'seo', ref: '#/components/schemas/PageSeo', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'ReorderPagesRequest',
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
    schema: 'SyncPageBlocksRequest',
    type: 'object',
    required: ['blocks'],
    properties: [
        new SA\Property(
            property: 'blocks',
            type: 'array',
            items: new SA\Items(ref: '#/components/schemas/PageBlock'),
        ),
    ]
)]
final class PageRequests
{
}
