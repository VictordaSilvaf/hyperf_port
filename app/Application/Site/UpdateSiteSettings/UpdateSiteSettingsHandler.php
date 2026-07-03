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

namespace App\Application\Site\UpdateSiteSettings;

use App\Application\Site\SitePublicCacheInterface;
use App\Domain\Site\Repository\SiteSettingsRepositoryInterface;
use App\Domain\Site\ValueObject\SiteSeoDefaults;

final class UpdateSiteSettingsHandler
{
    public function __construct(
        private readonly SiteSettingsRepositoryInterface $settings,
        private readonly SitePublicCacheInterface $cache,
    ) {
    }

    public function handle(UpdateSiteSettingsCommand $command): array
    {
        $current = $this->settings->get();
        $changes = [];
        $c = $command->changes;

        foreach (['nav', 'footer', 'social', 'branding'] as $field) {
            if (array_key_exists($field, $c)) {
                $changes[$field] = is_array($c[$field]) ? $c[$field] : null;
            }
        }

        if (array_key_exists('seo', $c) && is_array($c['seo'])) {
            $changes['seo'] = SiteSeoDefaults::fromArray($c['seo']);
        }

        $updated = $current->replace($changes);
        $this->settings->save($updated);
        $this->cache->bump();

        return [
            'data' => [
                'nav' => $updated->nav(),
                'footer' => $updated->footer(),
                'social' => $updated->social(),
                'branding' => $updated->branding(),
                'seo' => $updated->seo()->toArray(),
                'updated_at' => $updated->updatedAt()?->format(DATE_ATOM),
            ],
        ];
    }
}
