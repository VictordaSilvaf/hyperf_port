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
    schema: 'SiteSettings',
    type: 'object',
    properties: [
        new SA\Property(property: 'nav', type: 'array', items: new SA\Items(type: 'object')),
        new SA\Property(property: 'footer', type: 'object'),
        new SA\Property(property: 'social', type: 'object'),
        new SA\Property(property: 'branding', type: 'object'),
        new SA\Property(
            property: 'seo',
            type: 'object',
            properties: [
                new SA\Property(property: 'site_name', type: 'string', example: 'Victor Dev'),
                new SA\Property(property: 'default_meta_description', type: 'string', nullable: true),
                new SA\Property(property: 'default_og_image_id', type: 'string', format: 'uuid', nullable: true),
                new SA\Property(property: 'twitter_site', type: 'string', nullable: true),
                new SA\Property(property: 'google_site_verification', type: 'string', nullable: true),
                new SA\Property(property: 'locale', type: 'string', example: 'pt_BR'),
            ],
        ),
        new SA\Property(
            property: 'contact',
            type: 'object',
            properties: [
                new SA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                new SA\Property(property: 'phone', type: 'string', nullable: true),
                new SA\Property(property: 'whatsapp', type: 'string', nullable: true),
                new SA\Property(property: 'address', type: 'object', nullable: true),
                new SA\Property(property: 'notification_email', type: 'string', format: 'email', nullable: true),
            ],
        ),
        new SA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[SA\Schema(
    schema: 'SiteSettingsEnvelope',
    type: 'object',
    required: ['data'],
    properties: [
        new SA\Property(property: 'data', ref: '#/components/schemas/SiteSettings'),
    ]
)]
final class SiteSettings
{
}
