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

namespace App\Domain\Site\Repository;

use App\Domain\Site\Entity\SiteSettings;

interface SiteSettingsRepositoryInterface
{
    public function get(): SiteSettings;

    public function save(SiteSettings $settings): void;
}
