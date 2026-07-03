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

namespace App\Application\Page\DraftPage;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;

final class DraftPageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
        private readonly PagePresenter $presenter,
    ) {
    }

    public function handle(DraftPageCommand $command): array
    {
        $id = PageId::fromString($command->pageId);
        $page = $this->pages->findById($id);
        if ($page === null) {
            throw PageNotFoundException::withId($command->pageId);
        }

        $updated = $page->toDraft();
        $this->pages->save($updated);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($updated)];
    }
}
