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

namespace App\Application\Page\ReorderPages;

use App\Application\Page\PagePublicCacheInterface;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;

final class ReorderPagesHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
    ) {
    }

    public function handle(ReorderPagesCommand $command): void
    {
        foreach ($command->items as $item) {
            $id = PageId::fromString((string) $item['id']);
            if ($this->pages->findById($id) === null) {
                throw PageNotFoundException::withId((string) $item['id']);
            }
        }

        $this->pages->reorderPages($command->items);
        $this->cache->bump();
    }
}
