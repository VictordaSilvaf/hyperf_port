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
require_once __DIR__ . '/PageTestSupport.php';

use App\Application\Page\ArchivePage\ArchivePageCommand;
use App\Application\Page\DeletePage\DeletePageCommand;
use App\Application\Page\DraftPage\DraftPageCommand;
use App\Application\Page\DuplicatePage\DuplicatePageCommand;
use App\Application\Page\GetPage\GetPageHandler;
use App\Application\Page\GetPage\GetPageQuery;
use App\Application\Page\GetPageBySlug\GetPageBySlugHandler;
use App\Application\Page\ListPages\ListPagesHandler;
use App\Application\Page\ListPages\ListPagesQuery;
use App\Application\Page\PatchPage\PatchPageCommand;
use App\Application\Page\RestorePage\RestorePageCommand;
use App\Application\Page\UpdatePage\UpdatePageCommand;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Exception\PageSlugTakenException;
use App\Domain\Page\ValueObject\PageStatus;

test('create publish and fetch page', function () {
    $fixtures = pageFixtures();
    $id = seedPublishedPage($fixtures, pageCreateCommand('About Me', 'about-me'));

    $get = new GetPageHandler($fixtures['repo'], $fixtures['presenter']);
    $fetched = $get->handle(new GetPageQuery($id));

    expect($fetched['data']['status'])->toBe(PageStatus::Published->value);
    expect($fetched['data']['slug'])->toBe('about-me');
});

test('get page throws when missing', function () {
    $fixtures = pageFixtures();
    $get = new GetPageHandler($fixtures['repo'], $fixtures['presenter']);
    $get->handle(new GetPageQuery('a0000001-0000-4000-8000-000000000001'));
})->throws(PageNotFoundException::class);

test('update page replaces fields', function () {
    $fixtures = pageFixtures();
    $id = seedPage($fixtures);

    $result = $fixtures['update']->handle(new UpdatePageCommand(
        $id,
        'Updated Title',
        'updated-title',
        'full-width',
        ['meta_title' => 'SEO Title'],
        false,
        'published',
    ));

    expect($result['data']['title'])->toBe('Updated Title');
    expect($result['data']['slug'])->toBe('updated-title');
    expect($result['data']['layout'])->toBe('full-width');
    expect($result['data']['seo']['meta_title'])->toBe('SEO Title');
});

test('patch page applies partial changes', function () {
    $fixtures = pageFixtures();
    $id = seedPage($fixtures);

    $result = $fixtures['patch']->handle(new PatchPageCommand($id, [
        'title' => 'Patched Title',
        'order' => 5,
    ]));

    expect($result['data']['title'])->toBe('Patched Title');
    expect($result['data']['order'])->toBe(5);
    expect($result['data']['slug'])->toBe('about-me');
});

test('update rejects duplicate slug from another page', function () {
    $fixtures = pageFixtures();
    seedPage($fixtures, pageCreateCommand('First', 'first-page'));
    $secondId = seedPage($fixtures, pageCreateCommand('Second', 'second-page'));

    expect(fn () => $fixtures['update']->handle(new UpdatePageCommand(
        $secondId,
        'Second',
        'first-page',
        'default',
        null,
        false,
        'draft',
    )))->toThrow(PageSlugTakenException::class);
});

test('create rejects duplicate slug', function () {
    $fixtures = pageFixtures();
    seedPage($fixtures, pageCreateCommand('First', 'shared-slug'));

    expect(fn () => $fixtures['create']->handle(pageCreateCommand('Second', 'shared-slug')))
        ->toThrow(PageSlugTakenException::class);
});

test('soft delete hides page and restore brings it back', function () {
    $fixtures = pageFixtures();
    $id = seedPage($fixtures);
    $get = new GetPageHandler($fixtures['repo'], $fixtures['presenter']);

    $fixtures['delete']->handle(new DeletePageCommand($id));
    expect(fn () => $get->handle(new GetPageQuery($id)))->toThrow(PageNotFoundException::class);

    $restored = $fixtures['restore']->handle(new RestorePageCommand($id));
    expect($restored['data']['id'])->toBe($id);
    expect($get->handle(new GetPageQuery($id))['data']['slug'])->toBe('about-me');
});

test('force delete removes page permanently', function () {
    $fixtures = pageFixtures();
    $id = seedPage($fixtures);

    $fixtures['delete']->handle(new DeletePageCommand($id, true));

    expect(fn () => $fixtures['restore']->handle(new RestorePageCommand($id)))
        ->toThrow(PageNotFoundException::class);
});

test('archive and draft change page status', function () {
    $fixtures = pageFixtures();
    $id = seedPublishedPage($fixtures);

    $archived = $fixtures['archive']->handle(new ArchivePageCommand($id));
    expect($archived['data']['status'])->toBe(PageStatus::Archived->value);

    $drafted = $fixtures['draft']->handle(new DraftPageCommand($id));
    expect($drafted['data']['status'])->toBe(PageStatus::Draft->value);
});

test('duplicate page creates draft copy with unique slug', function () {
    $fixtures = pageFixtures();
    $id = seedPublishedPage($fixtures, pageCreateCommand('Original', 'original'));

    $copy = $fixtures['duplicate']->handle(new DuplicatePageCommand($id));

    expect($copy['data']['id'])->not->toBe($id);
    expect($copy['data']['slug'])->toBe('original-copy');
    expect($copy['data']['status'])->toBe(PageStatus::Draft->value);
    expect($copy['data']['title'])->toBe('Original (cópia)');
});

test('public list only includes published pages', function () {
    $fixtures = pageFixtures();
    seedPublishedPage($fixtures, pageCreateCommand('Public One', 'public-one'));
    seedPage($fixtures, pageCreateCommand('Draft Two', 'draft-two'));

    $list = new ListPagesHandler($fixtures['repo'], $fixtures['cache']);
    $public = $list->handle(new ListPagesQuery(publicOnly: true));
    $admin = $list->handle(new ListPagesQuery());

    expect($public['meta']['total'])->toBe(1);
    expect($public['data'][0]['slug'])->toBe('public-one');
    expect($admin['meta']['total'])->toBe(2);
});

test('get by slug rejects draft page for public access', function () {
    $fixtures = pageFixtures();
    seedPage($fixtures, pageCreateCommand('Secret', 'secret-draft'));

    $getBySlug = new GetPageBySlugHandler($fixtures['repo'], $fixtures['presenter'], $fixtures['cache']);
    $getBySlug->handle('secret-draft');
})->throws(PageNotFoundException::class);

test('is_home is exclusive when creating pages', function () {
    $fixtures = pageFixtures();
    $firstId = seedPage($fixtures, pageCreateCommand('Home', 'home', 'published', true));
    $secondId = seedPage($fixtures, pageCreateCommand('New Home', 'new-home', 'published', true));

    expect(pageIsHome($fixtures, $firstId))->toBeFalse();
    expect(pageIsHome($fixtures, $secondId))->toBeTrue();
});

test('is_home is exclusive when updating pages', function () {
    $fixtures = pageFixtures();
    $homeId = seedPage($fixtures, pageCreateCommand('Home', 'home', 'published', true));
    $otherId = seedPage($fixtures, pageCreateCommand('Other', 'other'));

    $fixtures['update']->handle(new UpdatePageCommand(
        $otherId,
        'Other',
        'other',
        'default',
        null,
        true,
        'published',
    ));

    expect(pageIsHome($fixtures, $homeId))->toBeFalse();
    expect(pageIsHome($fixtures, $otherId))->toBeTrue();
});

test('is_home is exclusive when patching pages', function () {
    $fixtures = pageFixtures();
    $homeId = seedPage($fixtures, pageCreateCommand('Home', 'home', 'published', true));
    $otherId = seedPage($fixtures, pageCreateCommand('Other', 'other'));

    $fixtures['patch']->handle(new PatchPageCommand($otherId, ['is_home' => true]));

    expect(pageIsHome($fixtures, $homeId))->toBeFalse();
    expect(pageIsHome($fixtures, $otherId))->toBeTrue();
});

test('only one home page exists in repository', function () {
    $fixtures = pageFixtures();
    seedPage($fixtures, pageCreateCommand('Home A', 'home-a', 'published', true));
    seedPage($fixtures, pageCreateCommand('Home B', 'home-b', 'published', true));
    seedPage($fixtures, pageCreateCommand('Home C', 'home-c', 'published', true));

    $homeCount = 0;
    foreach ($fixtures['repo']->paginate(1, 50)['items'] as $item) {
        if ($item['is_home']) {
            ++$homeCount;
        }
    }

    expect($homeCount)->toBe(1);
});
