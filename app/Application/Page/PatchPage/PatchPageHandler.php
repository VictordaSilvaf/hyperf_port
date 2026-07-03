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

namespace App\Application\Page\PatchPage;

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
use DateTimeImmutable;

final class PatchPageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePublicCacheInterface $cache,
        private readonly PagePresenter $presenter,
    ) {
    }

    public function handle(PatchPageCommand $command): array
    {
        $id = PageId::fromString($command->pageId);
        $page = $this->pages->findById($id);
        if ($page === null) {
            throw PageNotFoundException::withId($command->pageId);
        }

        $changes = [];
        $c = $command->changes;

        if (array_key_exists('title', $c)) {
            $changes['title'] = (string) $c['title'];
        }
        if (array_key_exists('slug', $c)) {
            $slug = PageSlug::fromString((string) $c['slug']);
            $existing = $this->pages->findBySlug($slug);
            if ($existing !== null && $existing->id()->value() !== $page->id()->value()) {
                throw PageSlugTakenException::forSlug($slug->value());
            }
            $changes['slug'] = $slug;
        }
        if (array_key_exists('layout', $c)) {
            $changes['layout'] = PageLayout::fromString((string) $c['layout']);
        }
        if (array_key_exists('seo', $c)) {
            $changes['seo'] = is_array($c['seo']) ? PageSeo::fromArray($c['seo']) : null;
        }
        if (array_key_exists('is_home', $c)) {
            $changes['is_home'] = (bool) $c['is_home'];
        }
        if (array_key_exists('status', $c)) {
            $changes['status'] = PageStatus::from((string) $c['status']);
        }
        if (array_key_exists('published_at', $c) && $c['published_at'] !== null) {
            $changes['published_at'] = new DateTimeImmutable((string) $c['published_at']);
        }
        if (array_key_exists('order', $c)) {
            $changes['sort_order'] = (int) $c['order'];
        }

        $updated = $page->replace($changes);

        if ($updated->isHome()) {
            $this->pages->clearHomeFlag($updated->id());
        }

        $this->pages->save($updated);
        $this->cache->bump();

        return ['data' => $this->presenter->toDetail($updated)];
    }
}
