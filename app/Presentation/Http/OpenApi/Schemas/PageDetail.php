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
    schema: 'PageSeo',
    type: 'object',
    properties: [
        new SA\Property(property: 'meta_title', type: 'string', nullable: true, maxLength: 70),
        new SA\Property(property: 'meta_description', type: 'string', nullable: true, maxLength: 160),
        new SA\Property(property: 'og_title', type: 'string', nullable: true),
        new SA\Property(property: 'og_description', type: 'string', nullable: true),
        new SA\Property(property: 'og_image_id', type: 'string', format: 'uuid', nullable: true),
        new SA\Property(property: 'canonical_url', type: 'string', format: 'uri', nullable: true),
        new SA\Property(property: 'robots', type: 'string', nullable: true, example: 'index,follow'),
        new SA\Property(property: 'twitter_card', type: 'string', nullable: true, enum: ['summary', 'summary_large_image']),
        new SA\Property(property: 'title', type: 'string', nullable: true, description: 'SEO resolvido (público)'),
        new SA\Property(property: 'description', type: 'string', nullable: true),
        new SA\Property(property: 'canonical', type: 'string', nullable: true),
        new SA\Property(property: 'open_graph', type: 'object', nullable: true),
        new SA\Property(property: 'twitter', type: 'object', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'PageBlock',
    type: 'object',
    required: ['type', 'payload'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid', nullable: true),
        new SA\Property(
            property: 'type',
            type: 'string',
            enum: ['hero', 'markdown', 'image', 'gallery', 'featured_projects', 'project_list', 'tech_stack', 'cta', 'contact_form', 'embed', 'spacer'],
            example: 'hero',
        ),
        new SA\Property(property: 'order', type: 'integer', example: 1),
        new SA\Property(property: 'payload', type: 'object', example: ['headline' => 'Olá, sou Victor']),
        new SA\Property(property: 'settings', type: 'object', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'PageDetail',
    type: 'object',
    required: ['id', 'title', 'slug', 'status'],
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'title', type: 'string', example: 'Início'),
        new SA\Property(property: 'slug', type: 'string', example: 'inicio'),
        new SA\Property(property: 'layout', type: 'string', enum: ['default', 'full-width', 'landing'], example: 'default'),
        new SA\Property(property: 'is_home', type: 'boolean', example: true),
        new SA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived']),
        new SA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
        new SA\Property(property: 'order', type: 'integer', example: 1),
        new SA\Property(property: 'sort_order', type: 'integer', nullable: true),
        new SA\Property(property: 'seo', ref: '#/components/schemas/PageSeo'),
        new SA\Property(property: 'blocks', type: 'array', items: new SA\Items(ref: '#/components/schemas/PageBlock')),
    ]
)]
#[SA\Schema(
    schema: 'PageDetailEnvelope',
    type: 'object',
    required: ['data'],
    properties: [
        new SA\Property(property: 'data', ref: '#/components/schemas/PageDetail'),
    ]
)]
#[SA\Schema(
    schema: 'PageSummary',
    type: 'object',
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'title', type: 'string'),
        new SA\Property(property: 'slug', type: 'string'),
        new SA\Property(property: 'status', type: 'string'),
        new SA\Property(property: 'is_home', type: 'boolean'),
        new SA\Property(property: 'sort_order', type: 'integer'),
        new SA\Property(property: 'published_at', type: 'string', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'PageListResponse',
    type: 'object',
    required: ['data', 'meta'],
    properties: [
        new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/PageSummary')),
        new SA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta'),
    ]
)]
#[SA\Schema(
    schema: 'BlockType',
    type: 'object',
    properties: [
        new SA\Property(property: 'type', type: 'string', example: 'hero'),
        new SA\Property(property: 'label', type: 'string', example: 'Hero'),
        new SA\Property(property: 'description', type: 'string', nullable: true),
    ]
)]
final class PageDetail
{
}
