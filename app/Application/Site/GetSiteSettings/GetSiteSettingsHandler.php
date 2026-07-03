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

namespace App\Application\Site\GetSiteSettings;

use App\Application\Site\SitePublicCacheInterface;
use App\Domain\Site\Repository\SiteSettingsRepositoryInterface;

final class GetSiteSettingsHandler
{
    private const CACHE_KEY = 'settings';

    public function __construct(
        private readonly SiteSettingsRepositoryInterface $settings,
        private readonly SitePublicCacheInterface $cache,
    ) {
    }

    public function handle(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $site = $this->settings->get();
        $payload = [
            'data' => [
                'nav' => $site->nav(),
                'footer' => $site->footer(),
                'social' => $site->social(),
                'branding' => $site->branding(),
                'seo' => $site->seo()->toArray(),
                'updated_at' => $site->updatedAt()?->format(DATE_ATOM),
            ],
        ];

        $this->cache->set(self::CACHE_KEY, $payload, 300);

        return $payload;
    }
}
