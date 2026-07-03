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

namespace App\Application\Page\Shared;

use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Page\Entity\Page;
use App\Domain\Page\Entity\PageBlock;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Domain\Site\Repository\SiteSettingsRepositoryInterface;
use App\Domain\Site\ValueObject\SiteSeoDefaults;
use App\Domain\Technology\Repository\TechnologyRepositoryInterface;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\Upload\ValueObject\UploadId;

use function Hyperf\Support\env;

final class PagePresenter
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly UploadRepositoryInterface $uploads,
        private readonly ProjectPresenter $projectPresenter,
        private readonly TechnologyRepositoryInterface $technologies,
        private readonly ProjectRepositoryInterface $projects,
        private readonly SiteSettingsRepositoryInterface $siteSettings,
    ) {
    }

    public function toDetail(Page $page, bool $resolveForPublic = false): array
    {
        $blocks = $this->pages->blocksFor($page->id());

        $data = [
            'id' => $page->id()->value(),
            'title' => $page->title(),
            'slug' => $page->slug()->value(),
            'layout' => $page->layout()->value,
            'is_home' => $page->isHome(),
            'status' => $page->status()->value,
            'published_at' => $page->publishedAt()?->format(DATE_ATOM),
            'order' => $page->sortOrder(),
            'blocks' => $resolveForPublic ? $this->enrichBlocks($blocks) : $this->serializeBlocks($blocks),
        ];

        if ($resolveForPublic) {
            $data['seo'] = $this->resolveSeo($page, $this->siteSettings->get()->seo(), $blocks);
        } else {
            $data['seo'] = $page->seo()?->toArray();
        }

        return $data;
    }

    /**
     * @param list<PageBlock> $blocks
     * @return list<array<string, mixed>>
     */
    public function enrichBlocks(array $blocks): array
    {
        return array_map(function (PageBlock $block): array {
            return [
                'id' => $block->id()->value(),
                'type' => $block->type(),
                'order' => $block->sortOrder(),
                'payload' => $this->enrichPayload($block->type(), $block->payload()),
                'settings' => $block->settings(),
            ];
        }, $blocks);
    }

    /**
     * @param list<PageBlock> $blocks
     * @return array<string, mixed>
     */
    public function resolveSeo(Page $page, SiteSeoDefaults $defaults, array $blocks = []): array
    {
        if ($blocks === []) {
            $blocks = $this->pages->blocksFor($page->id());
        }

        $pageSeo = $page->seo();
        $metaTitle = $pageSeo?->metaTitle() ?? $page->title();
        $metaDescription = $pageSeo?->metaDescription() ?? $defaults->defaultMetaDescription() ?? '';
        $ogTitle = $pageSeo?->ogTitle() ?? $pageSeo?->metaTitle() ?? $page->title();
        $ogDescription = $pageSeo?->ogDescription() ?? $pageSeo?->metaDescription() ?? $defaults->defaultMetaDescription() ?? '';
        $robots = $pageSeo?->robots() ?? 'index,follow';
        $twitterCard = $pageSeo?->twitterCard() ?? 'summary_large_image';

        $ogImageId = $pageSeo?->ogImageId()
            ?? $this->firstBlockImageId($blocks)
            ?? $defaults->defaultOgImageId();

        $ogImageUrl = $this->resolveUploadUrl($ogImageId);
        $canonical = $pageSeo?->canonicalUrl() ?? $this->buildCanonicalUrl($page);
        $documentTitle = $metaTitle . ' | ' . $defaults->siteName();

        return [
            'title' => $documentTitle,
            'description' => $metaDescription,
            'canonical' => $canonical,
            'robots' => $robots,
            'open_graph' => [
                'title' => $ogTitle,
                'description' => $ogDescription,
                'image_url' => $ogImageUrl,
                'type' => 'website',
                'locale' => $defaults->locale(),
            ],
            'twitter' => [
                'card' => $twitterCard,
                'site' => $defaults->twitterSite(),
                'title' => $ogTitle,
                'description' => $ogDescription,
                'image_url' => $ogImageUrl,
            ],
        ];
    }

    /**
     * @param list<PageBlock> $blocks
     * @return list<array<string, mixed>>
     */
    private function serializeBlocks(array $blocks): array
    {
        return array_map(static fn (PageBlock $block): array => [
            'id' => $block->id()->value(),
            'type' => $block->type(),
            'order' => $block->sortOrder(),
            'payload' => $block->payload(),
            'settings' => $block->settings(),
        ], $blocks);
    }

    /** @param array<string, mixed> $payload */
    private function enrichPayload(string $type, array $payload): array
    {
        return match ($type) {
            'hero' => $this->enrichHero($payload),
            'image' => $this->enrichImage($payload),
            'gallery' => $this->enrichGallery($payload),
            'featured_projects' => $this->enrichFeaturedProjects($payload),
            'project_list' => $this->enrichProjectList($payload),
            'tech_stack' => $this->enrichTechStack($payload),
            default => $payload,
        };
    }

    /** @param array<string, mixed> $payload */
    private function enrichHero(array $payload): array
    {
        if (isset($payload['image_id']) && is_string($payload['image_id'])) {
            $payload['image'] = $this->resolveUpload($payload['image_id']);
        }

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function enrichImage(array $payload): array
    {
        if (isset($payload['upload_id']) && is_string($payload['upload_id'])) {
            $payload['image'] = $this->resolveUpload($payload['upload_id']);
        }

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function enrichGallery(array $payload): array
    {
        if (! isset($payload['upload_ids']) || ! is_array($payload['upload_ids'])) {
            return $payload;
        }

        $payload['images'] = array_values(array_filter(array_map(
            fn ($id): ?array => is_string($id) ? $this->resolveUpload($id) : null,
            $payload['upload_ids'],
        )));

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function enrichFeaturedProjects(array $payload): array
    {
        if (! isset($payload['project_ids']) || ! is_array($payload['project_ids'])) {
            return $payload;
        }

        $payload['projects'] = array_values(array_filter(array_map(
            fn ($id): ?array => is_string($id) ? $this->projectPresenter->toSummaryFromId(ProjectId::fromString($id)) : null,
            $payload['project_ids'],
        )));

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function enrichProjectList(array $payload): array
    {
        $limit = isset($payload['limit']) && is_int($payload['limit']) ? $payload['limit'] : 6;
        $categoryId = isset($payload['category_id']) && is_string($payload['category_id']) && $payload['category_id'] !== ''
            ? $payload['category_id']
            : null;
        $tagId = isset($payload['tag_id']) && is_string($payload['tag_id']) && $payload['tag_id'] !== ''
            ? $payload['tag_id']
            : null;

        $result = $this->projects->paginate(new ProjectListFilter(
            page: 1,
            perPage: max($limit * 3, 50),
            status: ProjectStatus::Published,
            publicOnly: true,
            sort: 'sort_order',
        ));

        $items = [];
        foreach ($result['items'] as $item) {
            if (count($items) >= $limit) {
                break;
            }

            $projectId = ProjectId::fromString((string) $item['id']);
            if ($categoryId !== null && ! in_array($categoryId, $this->projects->categoryIdsFor($projectId), true)) {
                continue;
            }
            if ($tagId !== null && ! in_array($tagId, $this->projects->tagIdsFor($projectId), true)) {
                continue;
            }

            $items[] = $item;
        }

        $payload['projects'] = $items;

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function enrichTechStack(array $payload): array
    {
        if (! isset($payload['technology_ids']) || ! is_array($payload['technology_ids'])) {
            return $payload;
        }

        $ids = array_values(array_filter($payload['technology_ids'], static fn ($id): bool => is_string($id) && $id !== ''));
        $technologies = $this->technologies->findByIds($ids);
        $payload['technologies'] = array_map(static fn ($tech): array => [
            'id' => $tech->id()->value(),
            'name' => $tech->name(),
            'slug' => $tech->slug()->value(),
        ], $technologies);

        return $payload;
    }

    /** @return null|array{url: null|string, thumbnail_url: null|string} */
    private function resolveUpload(string $uploadId): ?array
    {
        $upload = $this->uploads->findById(UploadId::fromString($uploadId));
        if ($upload === null) {
            return null;
        }

        return [
            'url' => $upload->displayUrl(),
            'thumbnail_url' => $upload->displayThumbnailUrl(),
        ];
    }

    private function resolveUploadUrl(?string $uploadId): ?string
    {
        if ($uploadId === null) {
            return null;
        }

        $upload = $this->uploads->findById(UploadId::fromString($uploadId));

        return $upload?->displayUrl();
    }

    /** @param list<PageBlock> $blocks */
    private function firstBlockImageId(array $blocks): ?string
    {
        foreach ($blocks as $block) {
            $payload = $block->payload();
            if ($block->type() === 'hero' && isset($payload['image_id']) && is_string($payload['image_id']) && $payload['image_id'] !== '') {
                return $payload['image_id'];
            }
            if ($block->type() === 'image' && isset($payload['upload_id']) && is_string($payload['upload_id']) && $payload['upload_id'] !== '') {
                return $payload['upload_id'];
            }
        }

        return null;
    }

    private function buildCanonicalUrl(Page $page): string
    {
        $baseUrl = rtrim((string) env('APP_URL', 'http://127.0.0.1:9501'), '/');

        if ($page->isHome()) {
            return $baseUrl . '/';
        }

        return $baseUrl . '/pages/' . $page->slug()->value();
    }
}
