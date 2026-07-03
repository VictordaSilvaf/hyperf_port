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

namespace App\Application\Page\CreatePage;

use App\Application\Page\PagePublicCacheInterface;
use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Entity\Page;
use App\Domain\Page\Exception\PageSlugTakenException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageLayout;
use App\Domain\Page\ValueObject\PageSeo;
use App\Domain\Page\ValueObject\PageSlug;
use App\Domain\Page\ValueObject\PageStatus;

final class CreatePageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
        private readonly PagePresenter $presenter,
    ) {
    }

    public function handle(CreatePageCommand $command): array
    {
        $slugValue = $command->slug ?? PageSlug::normalize($command->title);
        $slug = PageSlug::fromString($slugValue);
        if ($this->pages->findBySlug($slug) !== null) {
            throw PageSlugTakenException::forSlug($slug->value());
        }

        $status = $command->status !== null
            ? PageStatus::from($command->status)
            : PageStatus::Draft;

        $layout = $command->layout !== null
            ? PageLayout::fromString($command->layout)
            : PageLayout::Default;

        $page = Page::create([
            'title' => $command->title,
            'slug' => $slug,
            'layout' => $layout,
            'seo' => PageSeo::fromArray($command->seo),
            'is_home' => $command->isHome,
            'status' => $status,
            'sort_order' => $this->pages->nextSortOrder(),
        ]);

        if ($page->isHome()) {
            $this->pages->clearHomeFlag($page->id());
        }

        $this->pages->save($page);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($page)];
    }
}
