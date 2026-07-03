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

namespace App\Application\Page\UpdatePage;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Exception\PageSlugTakenException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Page\ValueObject\PageLayout;
use App\Domain\Page\ValueObject\PageSeo;
use App\Domain\Page\ValueObject\PageSlug;
use App\Domain\Page\ValueObject\PageStatus;

final class UpdatePageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
        private readonly PagePresenter $presenter,
    ) {
    }

    public function handle(UpdatePageCommand $command): array
    {
        $id = PageId::fromString($command->pageId);
        $page = $this->pages->findById($id);
        if ($page === null) {
            throw PageNotFoundException::withId($command->pageId);
        }

        $slugValue = $command->slug ?? PageSlug::normalize($command->title);
        $slug = PageSlug::fromString($slugValue);
        $existing = $this->pages->findBySlug($slug);
        if ($existing !== null && $existing->id()->value() !== $page->id()->value()) {
            throw PageSlugTakenException::forSlug($slug->value());
        }

        $updated = $page->replace([
            'title' => $command->title,
            'slug' => $slug,
            'layout' => PageLayout::fromString($command->layout),
            'seo' => PageSeo::fromArray($command->seo),
            'is_home' => $command->isHome,
            'status' => $command->status !== null ? PageStatus::from($command->status) : $page->status(),
        ]);

        if ($updated->isHome()) {
            $this->pages->clearHomeFlag($updated->id());
        }

        $this->pages->save($updated);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($updated)];
    }
}
