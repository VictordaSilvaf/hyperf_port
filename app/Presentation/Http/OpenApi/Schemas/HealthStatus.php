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
    schema: 'HealthCheckItem',
    type: 'object',
    required: ['status'],
    properties: [
        new SA\Property(property: 'status', type: 'string', enum: ['pass', 'fail'], example: 'pass'),
        new SA\Property(property: 'message', type: 'string', nullable: true),
        new SA\Property(property: 'latency_ms', type: 'number', nullable: true, example: 12.5),
    ]
)]
#[SA\Schema(
    schema: 'HealthStatus',
    type: 'object',
    required: ['status', 'service', 'environment', 'timestamp', 'checks'],
    properties: [
        new SA\Property(property: 'status', type: 'string', enum: ['pass', 'fail'], example: 'pass'),
        new SA\Property(property: 'service', type: 'string', example: 'VictorDev'),
        new SA\Property(property: 'environment', type: 'string', example: 'dev'),
        new SA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-07-02T21:00:00+00:00'),
        new SA\Property(
            property: 'checks',
            type: 'object',
            additionalProperties: new SA\AdditionalProperties(ref: '#/components/schemas/HealthCheckItem'),
            example: ['app' => ['status' => 'pass']],
        ),
    ]
)]
final class HealthStatus
{
}
