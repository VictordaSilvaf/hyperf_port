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
use App\Application\Project\CreateProject\CreateProjectCommand;
use App\Application\Project\CreateProject\CreateProjectHandler;
use App\Application\Project\PublishProject\PublishProjectHandler;
use App\Application\Project\Shared\ProjectMediaUrls;
use App\Application\Project\Shared\ProjectPresenter;
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Application\Storage\ObjectStorageInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Upload\Entity\Upload;
use App\Infrastructure\Cache\ArrayProjectPublicCache;
use App\Infrastructure\Cache\ArrayProjectViewCounter;
use App\Infrastructure\Persistence\Category\InMemoryCategoryRepository;
use App\Infrastructure\Persistence\Project\InMemoryProjectRepository;
use App\Infrastructure\Persistence\Tag\InMemoryTagRepository;
use App\Infrastructure\Persistence\Technology\InMemoryTechnologyRepository;
use App\Infrastructure\Persistence\Upload\InMemoryUploadRepository;

final class NoOpPublicContentCacheInvalidator implements PublicContentCacheInvalidatorInterface
{
    public function invalidatePages(): void
    {
    }

    public function invalidateSite(): void
    {
    }

    public function invalidateProjects(): void
    {
    }
}

final class TestObjectStorage implements ObjectStorageInterface
{
    public function write(string $path, string $contents): void
    {
    }

    public function read(string $path): string
    {
        return '';
    }

    public function delete(string $path): void
    {
    }

    public function exists(string $path): bool
    {
        return false;
    }

    public function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return 'https://cdn.test/' . ltrim($path, '/');
    }
}

/**
 * @return array{
 *     repo: InMemoryProjectRepository,
 *     cache: ArrayProjectPublicCache,
 *     cacheInvalidator: NoOpPublicContentCacheInvalidator,
 *     viewCounter: ArrayProjectViewCounter,
 *     uploads: InMemoryUploadRepository,
 *     presenter: ProjectPresenter,
 *     mediaUrls: ProjectMediaUrls,
 *     create: CreateProjectHandler,
 *     publish: PublishProjectHandler,
 * }
 */
function projectFixtures(): array
{
    $repo = new InMemoryProjectRepository();
    $cache = new ArrayProjectPublicCache();
    $cacheInvalidator = new NoOpPublicContentCacheInvalidator();
    $viewCounter = new ArrayProjectViewCounter();
    $uploads = new InMemoryUploadRepository();
    $mediaUrls = new ProjectMediaUrls(new TestObjectStorage());
    $presenter = new ProjectPresenter(
        $repo,
        new InMemoryCategoryRepository(),
        new InMemoryTechnologyRepository(),
        new InMemoryTagRepository(),
        $uploads,
        $mediaUrls,
    );
    $create = new CreateProjectHandler($repo, $cache, $cacheInvalidator, $presenter);
    $publish = new PublishProjectHandler($repo, $cache, $cacheInvalidator, $presenter);

    return compact('repo', 'cache', 'cacheInvalidator', 'viewCounter', 'uploads', 'presenter', 'mediaUrls', 'create', 'publish');
}

function projectCreateCommand(
    string $title = 'Portfolio 3D',
    ?string $slug = 'portfolio-3d',
    string $status = 'draft',
    bool $featured = false,
): CreateProjectCommand {
    return new CreateProjectCommand(
        $title,
        $slug,
        'Short description',
        '# Markdown content',
        'https://github.com/example/repo',
        'https://demo.example.com',
        null,
        null,
        $status,
        $featured,
        [],
        [],
        [],
    );
}

function seedProject(array $fixtures, ?CreateProjectCommand $command = null): string
{
    $result = $fixtures['create']->handle($command ?? projectCreateCommand());

    return $result['data']['id'];
}

function seedPublishedProject(array $fixtures, ?CreateProjectCommand $command = null): string
{
    $id = seedProject($fixtures, $command);
    $fixtures['publish']->handle($id);

    return $id;
}

function seedUpload(array $fixtures, string $path = 'uploads/photo.jpg'): Upload
{
    $upload = Upload::create($path, 'https://cdn.example.com/' . $path, 'image/jpeg', 1024, 'photo.jpg');
    $fixtures['uploads']->save($upload);

    return $upload;
}

function projectViews(array $fixtures, string $projectId): int
{
    $project = $fixtures['repo']->findById(ProjectId::fromString($projectId), true);

    return $project?->views() ?? 0;
}
