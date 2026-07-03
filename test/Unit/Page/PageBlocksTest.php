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

use App\Application\Page\SyncPageBlocks\SyncPageBlocksCommand;
use App\Domain\Page\Exception\InvalidBlockPayloadException;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Exception\UnknownBlockTypeException;
use App\Domain\Page\ValueObject\PageId;

test('sync page blocks stores validated blocks in order', function () {
    $fixtures = pageFixtures();
    $pageId = seedPage($fixtures);
    $upload = seedPageUpload($fixtures);

    $result = $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        [
            'type' => 'hero',
            'payload' => ['headline' => 'Welcome', 'image_id' => $upload->id()->value()],
            'settings' => ['padding' => 'lg'],
        ],
        [
            'type' => 'markdown',
            'payload' => ['content' => '# Hello'],
        ],
        [
            'type' => 'spacer',
            'payload' => ['size' => 'md'],
        ],
    ]));

    expect($result['data']['blocks'])->toHaveCount(3);
    expect($result['data']['blocks'][0]['type'])->toBe('hero');
    expect($result['data']['blocks'][0]['order'])->toBe(0);
    expect($result['data']['blocks'][0]['payload']['headline'])->toBe('Welcome');
    expect($result['data']['blocks'][0]['settings'])->toBe(['padding' => 'lg']);
    expect($result['data']['blocks'][1]['type'])->toBe('markdown');
    expect($result['data']['blocks'][2]['type'])->toBe('spacer');
});

test('sync page blocks replaces previous blocks', function () {
    $fixtures = pageFixtures();
    $pageId = seedPage($fixtures);

    $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        ['type' => 'markdown', 'payload' => ['content' => 'Old']],
    ]));

    $result = $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        ['type' => 'markdown', 'payload' => ['content' => 'New']],
    ]));

    expect($result['data']['blocks'])->toHaveCount(1);
    expect($result['data']['blocks'][0]['payload']['content'])->toBe('New');
});

test('sync page blocks rejects invalid payload', function () {
    $fixtures = pageFixtures();
    $pageId = seedPage($fixtures);

    $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        ['type' => 'hero', 'payload' => ['subheadline' => 'Missing headline']],
    ]));
})->throws(InvalidBlockPayloadException::class);

test('sync page blocks rejects unknown block type', function () {
    $fixtures = pageFixtures();
    $pageId = seedPage($fixtures);

    $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        ['type' => 'unknown_block', 'payload' => []],
    ]));
})->throws(UnknownBlockTypeException::class);

test('sync page blocks throws when page is missing', function () {
    $fixtures = pageFixtures();

    $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand(
        'a0000001-0000-4000-8000-000000000099',
        [['type' => 'spacer', 'payload' => []]],
    ));
})->throws(PageNotFoundException::class);

test('sync page blocks rejects invalid image upload id', function () {
    $fixtures = pageFixtures();
    $pageId = seedPage($fixtures);

    $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        ['type' => 'image', 'payload' => ['upload_id' => 'not-a-uuid']],
    ]));
})->throws(InvalidArgumentException::class, 'Invalid upload id.');

test('repository blocks reflect synced state', function () {
    $fixtures = pageFixtures();
    $pageId = seedPage($fixtures);

    $fixtures['syncBlocks']->handle(new SyncPageBlocksCommand($pageId, [
        ['type' => 'cta', 'payload' => ['label' => 'Contact', 'href' => 'https://example.com/contact']],
    ]));

    $blocks = $fixtures['repo']->blocksFor(PageId::fromString($pageId));
    expect($blocks)->toHaveCount(1);
    expect($blocks[0]->type())->toBe('cta');
    expect($blocks[0]->payload()['label'])->toBe('Contact');
});
