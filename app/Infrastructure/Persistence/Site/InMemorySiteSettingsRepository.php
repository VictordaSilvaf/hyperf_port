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

namespace App\Infrastructure\Persistence\Site;

use App\Domain\Site\Entity\SiteSettings;
use App\Domain\Site\Repository\SiteSettingsRepositoryInterface;

final class InMemorySiteSettingsRepository implements SiteSettingsRepositoryInterface
{
    private ?SiteSettings $settings = null;

    public function get(): SiteSettings
    {
        return $this->settings ?? SiteSettings::restore([]);
    }

    public function save(SiteSettings $settings): void
    {
        $this->settings = $settings;
    }
}
