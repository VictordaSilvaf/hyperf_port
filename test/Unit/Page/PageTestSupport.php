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
use App\Application\Page\ArchivePage\ArchivePageHandler;
use App\Application\Page\CreatePage\CreatePageCommand;
use App\Application\Page\CreatePage\CreatePageHandler;
use App\Application\Page\DeletePage\DeletePageHandler;
use App\Application\Page\DraftPage\DraftPageHandler;
use App\Application\Page\DuplicatePage\DuplicatePageHandler;
use App\Application\Page\PatchPage\PatchPageHandler;
use App\Application\Page\PublishPage\PublishPageCommand;
use App\Application\Page\PublishPage\PublishPageHandler;
use App\Application\Page\RestorePage\RestorePageHandler;
use App\Application\Page\Shared\PagePresenter;
use App\Application\Page\SyncPageBlocks\SyncPageBlocksHandler;
use App\Application\Page\UpdatePage\UpdatePageHandler;
use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Page\ValueObject\PageId;
use App\Domain\Upload\Entity\Upload;
use App\Infrastructure\Cache\ArrayPagePublicCache;
use App\Infrastructure\Page\BlockRegistry;
use App\Infrastructure\Persistence\Category\InMemoryCategoryRepository;
use App\Infrastructure\Persistence\Page\InMemoryPageRepository;
use App\Infrastructure\Persistence\Project\InMemoryProjectRepository;
use App\Infrastructure\Persistence\Site\InMemorySiteSettingsRepository;
use App\Infrastructure\Persistence\Tag\InMemoryTagRepository;
use App\Infrastructure\Persistence\Technology\InMemoryTechnologyRepository;
use App\Infrastructure\Persistence\Upload\InMemoryUploadRepository;

const PAGE_TEST_UPLOAD_ID = 'a0000001-0000-4000-8000-000000000010';

/**
 * @return array{
 *     repo: InMemoryPageRepository,
 *     cache: ArrayPagePublicCache,
 *     registry: BlockRegistry,
 *     uploads: InMemoryUploadRepository,
 *     projects: InMemoryProjectRepository,
 *     siteSettings: InMemorySiteSettingsRepository,
 *     presenter: PagePresenter,
 *     create: CreatePageHandler,
 *     publish: PublishPageHandler,
 *     update: UpdatePageHandler,
 *     patch: PatchPageHandler,
 *     delete: DeletePageHandler,
 *     restore: RestorePageHandler,
 *     archive: ArchivePageHandler,
 *     draft: DraftPageHandler,
 *     duplicate: DuplicatePageHandler,
 *     syncBlocks: SyncPageBlocksHandler,
 * }
 */
function pageFixtures(): array
{
    $repo = new InMemoryPageRepository();
    $cache = new ArrayPagePublicCache();
    $registry = new BlockRegistry();
    $uploads = new InMemoryUploadRepository();
    $projects = new InMemoryProjectRepository();
    $siteSettings = new InMemorySiteSettingsRepository();

    $projectPresenter = new ProjectPresenter(
        $projects,
        new InMemoryCategoryRepository(),
        new InMemoryTechnologyRepository(),
        new InMemoryTagRepository(),
        $uploads,
    );

    $presenter = new PagePresenter(
        $repo,
        $uploads,
        $projectPresenter,
        new InMemoryTechnologyRepository(),
        $projects,
        $siteSettings,
    );

    $create = new CreatePageHandler($repo, $cache, $presenter);
    $publish = new PublishPageHandler($repo, $cache, $presenter);
    $update = new UpdatePageHandler($repo, $cache, $presenter);
    $patch = new PatchPageHandler($repo, $cache, $presenter);
    $delete = new DeletePageHandler($repo, $cache);
    $restore = new RestorePageHandler($repo, $cache, $presenter);
    $archive = new ArchivePageHandler($repo, $cache, $presenter);
    $draft = new DraftPageHandler($repo, $cache, $presenter);
    $duplicate = new DuplicatePageHandler($repo, $presenter);
    $syncBlocks = new SyncPageBlocksHandler($repo, $registry, $cache, $presenter);

    return compact(
        'repo',
        'cache',
        'registry',
        'uploads',
        'projects',
        'siteSettings',
        'presenter',
        'create',
        'publish',
        'update',
        'patch',
        'delete',
        'restore',
        'archive',
        'draft',
        'duplicate',
        'syncBlocks',
    );
}

function pageCreateCommand(
    string $title = 'About Me',
    ?string $slug = 'about-me',
    string $status = 'draft',
    bool $isHome = false,
    ?string $layout = 'default',
    ?array $seo = null,
): CreatePageCommand {
    return new CreatePageCommand(
        $title,
        $slug,
        $layout,
        $seo,
        $isHome,
        $status,
    );
}

function seedPage(array $fixtures, ?CreatePageCommand $command = null): string
{
    $result = $fixtures['create']->handle($command ?? pageCreateCommand());

    return $result['data']['id'];
}

function seedPublishedPage(array $fixtures, ?CreatePageCommand $command = null): string
{
    $id = seedPage($fixtures, $command);
    $fixtures['publish']->handle(new PublishPageCommand($id));

    return $id;
}

function seedPageUpload(array $fixtures, string $path = 'uploads/hero.jpg'): Upload
{
    $upload = Upload::create($path, 'https://cdn.example.com/' . $path, 'image/jpeg', 2048, 'hero.jpg');
    $fixtures['uploads']->save($upload);

    return $upload;
}

function pageIsHome(array $fixtures, string $pageId): bool
{
    $page = $fixtures['repo']->findById(PageId::fromString($pageId));

    return $page?->isHome() ?? false;
}
