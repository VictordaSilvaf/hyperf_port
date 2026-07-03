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

namespace App\Application\Page\GetPageBySlug;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageSlug;

final class GetPageBySlugHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePresenter $presenter,
        private readonly PagePublicCacheInterface $cache,
    ) {
    }

    public function handle(string $slug): array
    {
        $cacheKey = 'slug:' . $slug;
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $page = $this->pages->findBySlug(PageSlug::fromString($slug), true);
        if ($page === null) {
            throw PageNotFoundException::withSlug($slug);
        }

        $payload = ['data' => $this->presenter->toDetail($page, true)];
        $this->cache->set($cacheKey, $payload, 300);

        return $payload;
    }
}
