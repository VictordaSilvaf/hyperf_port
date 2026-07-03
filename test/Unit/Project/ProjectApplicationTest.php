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
use App\Application\Project\GetProject\GetProjectHandler;
use App\Application\Project\PublishProject\PublishProjectHandler;
use App\Application\Project\Shared\ProjectPresenter;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Infrastructure\Cache\ArrayProjectPublicCache;
use App\Infrastructure\Persistence\Category\InMemoryCategoryRepository;
use App\Infrastructure\Persistence\Project\InMemoryProjectRepository;
use App\Infrastructure\Persistence\Tag\InMemoryTagRepository;
use App\Infrastructure\Persistence\Technology\InMemoryTechnologyRepository;
use App\Infrastructure\Persistence\Upload\InMemoryUploadRepository;

test('create publish and fetch project', function () {
    $repo = new InMemoryProjectRepository();
    $cache = new ArrayProjectPublicCache();
    $presenter = new ProjectPresenter(
        $repo,
        new InMemoryCategoryRepository(),
        new InMemoryTechnologyRepository(),
        new InMemoryTagRepository(),
        new InMemoryUploadRepository(),
    );
    $create = new CreateProjectHandler($repo, $cache, $presenter);
    $publish = new PublishProjectHandler($repo, $cache, $presenter);
    $get = new GetProjectHandler($repo, $presenter);

    $result = $create->handle(new CreateProjectCommand(
        'Portfolio 3D',
        'portfolio-3d',
        'Meu portfolio',
        '# Markdown',
        null,
        null,
        null,
        null,
        'draft',
        true,
        [],
        [],
        [],
    ));
    $id = $result['data']['id'];
    $publish->handle($id);

    $fetched = $get->handle($id);
    expect($fetched['data']['status'])->toBe(ProjectStatus::Published->value);
    expect($fetched['data']['slug'])->toBe('portfolio-3d');
});

test('get project throws when missing', function () {
    $repo = new InMemoryProjectRepository();
    $presenter = new ProjectPresenter(
        $repo,
        new InMemoryCategoryRepository(),
        new InMemoryTechnologyRepository(),
        new InMemoryTagRepository(),
        new InMemoryUploadRepository(),
    );
    $get = new GetProjectHandler($repo, $presenter);
    $get->handle('a0000001-0000-4000-8000-000000000001');
})->throws(ProjectNotFoundException::class);
