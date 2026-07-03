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
require_once __DIR__ . '/ProjectTestSupport.php';

use App\Application\Project\ArchiveProject\ArchiveProjectHandler;
use App\Application\Project\DeleteProject\DeleteProjectHandler;
use App\Application\Project\DraftProject\DraftProjectHandler;
use App\Application\Project\DuplicateProject\DuplicateProjectHandler;
use App\Application\Project\GetProject\GetProjectHandler;
use App\Application\Project\GetProjectBySlug\GetProjectBySlugHandler;
use App\Application\Project\GetProjectStatistics\GetProjectStatisticsHandler;
use App\Application\Project\ListProjects\ListProjectsHandler;
use App\Application\Project\PatchProject\PatchProjectCommand;
use App\Application\Project\PatchProject\PatchProjectHandler;
use App\Application\Project\RestoreProject\RestoreProjectHandler;
use App\Application\Project\UpdateProject\UpdateProjectCommand;
use App\Application\Project\UpdateProject\UpdateProjectHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Exception\ProjectSlugTakenException;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectStatus;

test('create publish and fetch project', function () {
    $fixtures = projectFixtures();
    $id = seedPublishedProject($fixtures, projectCreateCommand('Portfolio 3D', 'portfolio-3d'));

    $get = new GetProjectHandler($fixtures['repo'], $fixtures['presenter']);
    $fetched = $get->handle($id);

    expect($fetched['data']['status'])->toBe(ProjectStatus::Published->value);
    expect($fetched['data']['slug'])->toBe('portfolio-3d');
});

test('get project throws when missing', function () {
    $fixtures = projectFixtures();
    $get = new GetProjectHandler($fixtures['repo'], $fixtures['presenter']);
    $get->handle('a0000001-0000-4000-8000-000000000001');
})->throws(ProjectNotFoundException::class);

test('update project replaces fields and syncs taxonomies', function () {
    $fixtures = projectFixtures();
    $id = seedProject($fixtures);

    $update = new UpdateProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $result = $update->handle(new UpdateProjectCommand(
        $id,
        'Updated Title',
        'updated-title',
        'New description',
        '## Updated',
        'https://github.com/new/repo',
        'https://new.demo',
        'thumb.jpg',
        'cover.jpg',
        'published',
        true,
        ['cat-1'],
        ['tech-1'],
        ['tag-1'],
    ));

    expect($result['data']['title'])->toBe('Updated Title');
    expect($result['data']['slug'])->toBe('updated-title');
    expect($result['data']['featured'])->toBeTrue();
    expect($fixtures['repo']->categoryIdsFor(ProjectId::fromString($id)))->toBe(['cat-1']);
    expect($fixtures['repo']->technologyIdsFor(ProjectId::fromString($id)))->toBe(['tech-1']);
    expect($fixtures['repo']->tagIdsFor(ProjectId::fromString($id)))->toBe(['tag-1']);
});

test('patch project applies partial changes', function () {
    $fixtures = projectFixtures();
    $id = seedProject($fixtures);

    $patch = new PatchProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $result = $patch->handle(new PatchProjectCommand($id, [
        'title' => 'Patched Title',
        'featured' => true,
        'order' => 5,
    ]));

    expect($result['data']['title'])->toBe('Patched Title');
    expect($result['data']['featured'])->toBeTrue();
    expect($result['data']['order'])->toBe(5);
    expect($result['data']['slug'])->toBe('portfolio-3d');
});

test('update rejects duplicate slug from another project', function () {
    $fixtures = projectFixtures();
    seedProject($fixtures, projectCreateCommand('First', 'first-project'));
    $secondId = seedProject($fixtures, projectCreateCommand('Second', 'second-project'));

    $update = new UpdateProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);

    expect(fn () => $update->handle(new UpdateProjectCommand(
        $secondId,
        'Second',
        'first-project',
        null,
        null,
        null,
        null,
        null,
        null,
        'draft',
        false,
        [],
        [],
        [],
    )))->toThrow(ProjectSlugTakenException::class);
});

test('soft delete hides project and restore brings it back', function () {
    $fixtures = projectFixtures();
    $id = seedProject($fixtures);

    $delete = new DeleteProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator']);
    $restore = new RestoreProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $get = new GetProjectHandler($fixtures['repo'], $fixtures['presenter']);

    $delete->handle($id);
    expect(fn () => $get->handle($id))->toThrow(ProjectNotFoundException::class);

    $restored = $restore->handle($id);
    expect($restored['data']['id'])->toBe($id);
    expect($get->handle($id)['data']['slug'])->toBe('portfolio-3d');
});

test('force delete removes project permanently', function () {
    $fixtures = projectFixtures();
    $id = seedProject($fixtures);

    $delete = new DeleteProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator']);
    $restore = new RestoreProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);

    $delete->handle($id, true);

    expect(fn () => $restore->handle($id))->toThrow(ProjectNotFoundException::class);
});

test('archive and draft change project status', function () {
    $fixtures = projectFixtures();
    $id = seedPublishedProject($fixtures);

    $archive = new ArchiveProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $draft = new DraftProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);

    $archived = $archive->handle($id);
    expect($archived['data']['status'])->toBe(ProjectStatus::Archived->value);

    $drafted = $draft->handle($id);
    expect($drafted['data']['status'])->toBe(ProjectStatus::Draft->value);
});

test('duplicate project creates draft copy with unique slug', function () {
    $fixtures = projectFixtures();
    $id = seedPublishedProject($fixtures, projectCreateCommand('Original', 'original'));
    $fixtures['repo']->syncTags(ProjectId::fromString($id), ['tag-a']);

    $duplicate = new DuplicateProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $copy = $duplicate->handle($id);

    expect($copy['data']['id'])->not->toBe($id);
    expect($copy['data']['slug'])->toBe('original-copy');
    expect($copy['data']['status'])->toBe(ProjectStatus::Draft->value);
    expect($copy['data']['title'])->toBe('Original (cópia)');
    expect($fixtures['repo']->tagIdsFor(ProjectId::fromString($copy['data']['id'])))->toBe(['tag-a']);
});

test('public list only includes published projects', function () {
    $fixtures = projectFixtures();
    seedPublishedProject($fixtures, projectCreateCommand('Public One', 'public-one'));
    seedProject($fixtures, projectCreateCommand('Draft Two', 'draft-two'));

    $list = new ListProjectsHandler($fixtures['repo'], $fixtures['cache']);
    $public = $list->handle(new ProjectListFilter(publicOnly: true));
    $admin = $list->handle(new ProjectListFilter());

    expect($public['meta']['total'])->toBe(1);
    expect($public['data'][0]['slug'])->toBe('public-one');
    expect($admin['meta']['total'])->toBe(2);
});

test('statistics aggregate counts by status', function () {
    $fixtures = projectFixtures();
    seedPublishedProject($fixtures, projectCreateCommand('Pub', 'pub', 'published', true));
    seedProject($fixtures, projectCreateCommand('Draft', 'draft'));
    $archivedId = seedPublishedProject($fixtures, projectCreateCommand('Old', 'old'));
    (new ArchiveProjectHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']))->handle($archivedId);

    $stats = (new GetProjectStatisticsHandler($fixtures['repo']))->handle();

    expect($stats)->toMatchArray([
        'published' => 1,
        'draft' => 1,
        'archived' => 1,
        'featured' => 1,
        'views' => 0,
    ]);
});

test('get by slug rejects draft project for public access', function () {
    $fixtures = projectFixtures();
    seedProject($fixtures, projectCreateCommand('Secret', 'secret-draft'));

    $getBySlug = new GetProjectBySlugHandler(
        $fixtures['repo'],
        $fixtures['presenter'],
        $fixtures['cache'],
        $fixtures['viewCounter'],
    );

    $getBySlug->handle('secret-draft');
})->throws(ProjectNotFoundException::class);
