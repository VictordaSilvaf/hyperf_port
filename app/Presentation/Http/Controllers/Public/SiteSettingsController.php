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

namespace App\Presentation\Http\Controllers\Public;

use App\Application\Site\GetSiteSettings\GetSiteSettingsHandler;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;

#[SA\HyperfServer('openapi')]
final class SiteSettingsController extends AbstractController
{
    #[Inject]
    protected GetSiteSettingsHandler $getSiteSettings;

    #[SA\Get(path: '/api/v1/site/settings', summary: 'Site settings públicos', tags: ['Pages (Public)'])]
    #[SA\Response(response: 200, description: 'Settings', content: new SA\JsonContent(ref: '#/components/schemas/SiteSettingsEnvelope'))]
    public function show(): array
    {
        return $this->getSiteSettings->handle();
    }
}
