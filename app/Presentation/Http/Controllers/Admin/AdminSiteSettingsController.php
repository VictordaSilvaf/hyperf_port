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

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Site\GetSiteSettings\GetSiteSettingsHandler;
use App\Application\Site\UpdateSiteSettings\UpdateSiteSettingsCommand;
use App\Application\Site\UpdateSiteSettings\UpdateSiteSettingsHandler;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\UpdateSiteSettingsRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;

#[SA\HyperfServer('openapi')]
final class AdminSiteSettingsController extends AbstractController
{
    #[Inject]
    protected GetSiteSettingsHandler $getSiteSettings;

    #[Inject]
    protected UpdateSiteSettingsHandler $updateSiteSettings;

    #[SA\Get(path: '/api/v1/admin/site/settings', summary: 'Obter site settings', description: 'Requires permission: site.update', security: OpenApiRefs::BEARER, tags: ['Admin Site'])]
    #[SA\Response(response: 200, description: 'Settings', content: new SA\JsonContent(ref: '#/components/schemas/SiteSettingsEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(): array
    {
        return $this->getSiteSettings->handle();
    }

    #[SA\Put(path: '/api/v1/admin/site/settings', summary: 'Actualizar site settings', description: 'Requires permission: site.update', security: OpenApiRefs::BEARER, tags: ['Admin Site'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/UpdateSiteSettingsRequest'))]
    #[SA\Response(response: 200, description: 'Settings actualizados', content: new SA\JsonContent(ref: '#/components/schemas/SiteSettingsEnvelope'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function update(UpdateSiteSettingsRequest $request): array
    {
        return $this->updateSiteSettings->handle(new UpdateSiteSettingsCommand($request->validated()));
    }
}
