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

namespace App\Application\Page\ListBlockTypes;

use App\Application\Page\BlockRegistryInterface;
use App\Application\Page\PagePublicCacheInterface;

final class ListBlockTypesHandler
{
    private const CACHE_KEY = 'block-types';

    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly BlockRegistryInterface $registry,
        private readonly PagePublicCacheInterface $cache,
    ) {
    }

    public function handle(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $payload = ['data' => $this->registry->metadata()];
        $this->cache->set(self::CACHE_KEY, $payload, self::CACHE_TTL);

        return $payload;
    }
}
