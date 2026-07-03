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

namespace App\Application\Page\DuplicatePage;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Exception\PageSlugTakenException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageSlug;

final class DuplicatePageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
        private readonly PagePresenter $presenter,
    ) {
    }

    public function handle(DuplicatePageCommand $command): array
    {
        $id = PageId::fromString($command->pageId);
        $source = $this->pages->findById($id);
        if ($source === null) {
            throw PageNotFoundException::withId($command->pageId);
        }

        $baseSlug = $source->slug()->value() . '-copy';
        $slug = PageSlug::fromString($baseSlug);
        $attempt = 1;
        while ($this->pages->findBySlug($slug) !== null) {
            ++$attempt;
            if ($attempt > 20) {
                throw PageSlugTakenException::forSlug($baseSlug);
            }
            $slug = PageSlug::fromString($baseSlug . '-' . $attempt);
        }

        $copy = $source->duplicateAsDraft($slug, $this->pages->nextSortOrder());
        $this->pages->save($copy);

        $blocks = [];
        foreach ($this->pages->blocksFor($id) as $block) {
            $blocks[] = [
                'type' => $block->type(),
                'payload' => $block->payload(),
                'settings' => $block->settings(),
            ];
        }
        if ($blocks !== []) {
            $this->pages->syncBlocks($copy->id(), $blocks);
        }

        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($copy)];
    }
}
