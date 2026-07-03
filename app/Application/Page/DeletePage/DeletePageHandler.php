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

namespace App\Application\Page\DeletePage;

use App\Application\Page\PagePublicCacheInterface;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;

final class DeletePageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
    ) {
    }

    public function handle(DeletePageCommand $command): void
    {
        $id = PageId::fromString($command->pageId);
        $page = $this->pages->findById($id, $command->force);
        if ($page === null && ! $command->force) {
            $page = $this->pages->findById($id, true);
        }
        if ($page === null) {
            throw PageNotFoundException::withId($command->pageId);
        }

        if ($command->force) {
            $this->pages->forceDelete($id);
        } else {
            $this->pages->softDelete($id);
        }

        $this->cache->bump();
    }
}
