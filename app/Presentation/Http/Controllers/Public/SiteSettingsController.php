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

final class SiteSettingsController extends AbstractController
{
    #[Inject]
    protected GetSiteSettingsHandler $getSiteSettings;

    public function show(): array
    {
        return $this->getSiteSettings->handle();
    }
}
