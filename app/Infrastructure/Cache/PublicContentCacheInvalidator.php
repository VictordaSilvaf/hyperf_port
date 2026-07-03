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

namespace App\Infrastructure\Cache;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Project\ProjectPublicCacheInterface;
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Application\Site\SitePublicCacheInterface;

final class PublicContentCacheInvalidator implements PublicContentCacheInvalidatorInterface
{
    public function __construct(
        private readonly PagePublicCacheInterface $pageCache,
        private readonly SitePublicCacheInterface $siteCache,
        private readonly ProjectPublicCacheInterface $projectCache,
    ) {
    }

    public function invalidatePages(): void
    {
        $this->pageCache->bump();
    }

    public function invalidateSite(): void
    {
        $this->siteCache->bump();
    }

    public function invalidateProjects(): void
    {
        $this->projectCache->bump();
    }
}
