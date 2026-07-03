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
use App\Presentation\Http\Requests\Admin\UpdateSiteSettingsRequest;
use Hyperf\Di\Annotation\Inject;

final class AdminSiteSettingsController extends AbstractController
{
    #[Inject]
    protected GetSiteSettingsHandler $getSiteSettings;

    #[Inject]
    protected UpdateSiteSettingsHandler $updateSiteSettings;

    public function show(): array
    {
        return $this->getSiteSettings->handle();
    }

    public function update(UpdateSiteSettingsRequest $request): array
    {
        return $this->updateSiteSettings->handle(new UpdateSiteSettingsCommand($request->validated()));
    }
}
