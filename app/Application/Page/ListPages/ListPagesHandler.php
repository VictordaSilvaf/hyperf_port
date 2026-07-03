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

namespace App\Application\Page\ListPages;

use App\Application\Page\PagePublicCacheInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;

final class ListPagesHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
    ) {
    }

    public function handle(ListPagesQuery $query): array
    {
        $cacheKey = 'nav-list';
        if ($query->publicOnly) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $result = $this->pages->paginate($query->page, $query->perPage, $query->publicOnly);
        $payload = [
            'data' => $result['items'],
            'meta' => [
                'total' => $result['total'],
                'page' => $query->page,
                'per_page' => $query->perPage,
            ],
        ];

        if ($query->publicOnly) {
            $this->cache->set($cacheKey, $payload, 300);
        }

        return $payload;
    }
}
