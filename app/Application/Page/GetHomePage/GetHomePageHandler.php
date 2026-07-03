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

namespace App\Application\Page\GetHomePage;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Repository\PageRepositoryInterface;

final class GetHomePageHandler
{
    private const CACHE_KEY = 'home';

    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePresenter $presenter,
        private readonly PagePublicCacheInterface $cache,
    ) {
    }

    public function handle(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $page = $this->pages->findHomePage(true);
        if ($page === null) {
            throw PageNotFoundException::withSlug('home');
        }

        $payload = ['data' => $this->presenter->toDetail($page, true)];
        $this->cache->set(self::CACHE_KEY, $payload, 300);

        return $payload;
    }
}
