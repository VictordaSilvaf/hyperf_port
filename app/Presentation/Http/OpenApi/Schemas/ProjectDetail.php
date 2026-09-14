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
    schema: 'ProjectImage',
    type: 'object',
    required: ['id', 'upload_id', 'order'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'upload_id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'caption', type: 'string', nullable: true, example: 'Tela inicial'),
        new SA\Property(property: 'order', type: 'integer', example: 1),
        new SA\Property(property: 'url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'path', type: 'string', nullable: true, example: 'uploads/2026/07/screen.png'),
    ]
)]
#[SA\Schema(
    schema: 'ProjectSummary',
    type: 'object',
    required: ['id', 'title', 'slug', 'status'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'title', type: 'string', example: 'Portfolio 3D'),
        new SA\Property(property: 'slug', type: 'string', example: 'portfolio-3d'),
        new SA\Property(property: 'description', type: 'string', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived']),
        new SA\Property(property: 'featured', type: 'boolean', example: true),
        new SA\Property(property: 'sort_order', type: 'integer', example: 1),
        new SA\Property(property: 'order', type: 'integer', example: 1),
        new SA\Property(property: 'thumbnail', type: 'string', nullable: true),
        new SA\Property(property: 'cover', type: 'string', nullable: true),
        new SA\Property(property: 'published_at', type: 'string', nullable: true),
        new SA\Property(property: 'views', type: 'integer', example: 1203),
        new SA\Property(property: 'created_at', type: 'string', nullable: true),
        new SA\Property(property: 'updated_at', type: 'string', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'ProjectDetail',
    type: 'object',
    required: ['id', 'title', 'slug', 'status'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'title', type: 'string'),
        new SA\Property(property: 'slug', type: 'string'),
        new SA\Property(property: 'description', type: 'string', nullable: true),
        new SA\Property(property: 'content', type: 'string', nullable: true),
        new SA\Property(property: 'repository_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'demo_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'thumbnail', type: 'string', nullable: true),
        new SA\Property(property: 'cover', type: 'string', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived']),
        new SA\Property(property: 'featured', type: 'boolean'),
        new SA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
        new SA\Property(property: 'order', type: 'integer'),
        new SA\Property(property: 'views', type: 'integer'),
        new SA\Property(property: 'categories', type: 'array', items: new SA\Items(ref: '#/components/schemas/TaxonomyItem')),
        new SA\Property(property: 'technologies', type: 'array', items: new SA\Items(ref: '#/components/schemas/TaxonomyItem')),
        new SA\Property(property: 'tags', type: 'array', items: new SA\Items(ref: '#/components/schemas/TaxonomyItem')),
        new SA\Property(property: 'images', type: 'array', items: new SA\Items(ref: '#/components/schemas/ProjectImage')),
    ]
)]
#[SA\Schema(
    schema: 'ProjectDetailEnvelope',
    type: 'object',
    required: ['data'],
    properties: [
        new SA\Property(property: 'data', ref: '#/components/schemas/ProjectDetail'),
    ]
)]
#[SA\Schema(
    schema: 'ProjectListResponse',
    type: 'object',
    required: ['data', 'meta'],
    properties: [
        new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/ProjectSummary')),
        new SA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta'),
    ]
)]
#[SA\Schema(
    schema: 'ProjectStatistics',
    type: 'object',
    properties: [
        new SA\Property(property: 'published', type: 'integer', example: 10),
        new SA\Property(property: 'draft', type: 'integer', example: 2),
        new SA\Property(property: 'archived', type: 'integer', example: 1),
        new SA\Property(property: 'views', type: 'integer', example: 5000),
        new SA\Property(property: 'featured', type: 'integer', example: 3),
    ]
)]
final class ProjectDetail
{
}
