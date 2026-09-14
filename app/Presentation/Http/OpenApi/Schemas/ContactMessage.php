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
    schema: 'ContactMessageSummary',
    type: 'object',
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'name', type: 'string'),
        new SA\Property(property: 'email', type: 'string', format: 'email'),
        new SA\Property(property: 'subject', type: 'string', nullable: true),
        new SA\Property(property: 'status', type: 'string', enum: ['new', 'read', 'archived']),
        new SA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[SA\Schema(
    schema: 'ContactMessage',
    type: 'object',
    properties: [
        new SA\Property(property: 'id', type: 'string', format: 'uuid'),
        new SA\Property(property: 'name', type: 'string'),
        new SA\Property(property: 'email', type: 'string', format: 'email'),
        new SA\Property(property: 'subject', type: 'string', nullable: true),
        new SA\Property(property: 'body', type: 'string'),
        new SA\Property(property: 'status', type: 'string', enum: ['new', 'read', 'archived']),
        new SA\Property(property: 'ip_address', type: 'string', nullable: true),
        new SA\Property(property: 'user_agent', type: 'string', nullable: true),
        new SA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new SA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'ContactMessageList',
    type: 'object',
    required: ['data', 'meta'],
    properties: [
        new SA\Property(property: 'data', type: 'array', items: new SA\Items(ref: '#/components/schemas/ContactMessageSummary')),
        new SA\Property(property: 'meta', ref: '#/components/schemas/PaginatedMeta'),
    ]
)]
#[SA\Schema(
    schema: 'ContactMessageEnvelope',
    type: 'object',
    required: ['data'],
    properties: [
        new SA\Property(property: 'data', ref: '#/components/schemas/ContactMessage'),
    ]
)]
final class ContactMessage
{
}
