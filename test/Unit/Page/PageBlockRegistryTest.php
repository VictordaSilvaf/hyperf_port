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

use App\Domain\Page\Exception\InvalidBlockPayloadException;
use App\Domain\Page\Exception\UnknownBlockTypeException;
use App\Infrastructure\Page\BlockRegistry;

test('block registry exposes all supported block types', function () {
    $registry = new BlockRegistry();
    $types = array_map(static fn ($item): string => $item['type'], $registry->metadata());

    expect($types)->toBe([
        'hero',
        'markdown',
        'image',
        'gallery',
        'featured_projects',
        'project_list',
        'tech_stack',
        'cta',
        'contact_form',
        'embed',
        'spacer',
    ]);
});

test('hero block validator accepts valid payload', function () {
    $registry = new BlockRegistry();

    $registry->validate('hero', ['headline' => 'Welcome']);
    $registry->validate('hero', [
        'headline' => 'Welcome',
        'subheadline' => 'Subtitle',
        'image_id' => PAGE_TEST_UPLOAD_ID,
        'cta' => ['label' => 'Learn more', 'href' => 'https://example.com'],
    ]);

    expect(true)->toBeTrue();
});

test('hero block validator rejects missing headline', function () {
    (new BlockRegistry())->validate('hero', []);
})->throws(InvalidBlockPayloadException::class);

test('markdown block validator accepts and rejects payloads', function () {
    $registry = new BlockRegistry();

    $registry->validate('markdown', ['content' => '# Hello']);

    $registry->validate('markdown', []);
})->throws(InvalidBlockPayloadException::class);

test('image block validator accepts and rejects payloads', function () {
    $registry = new BlockRegistry();

    $registry->validate('image', ['upload_id' => PAGE_TEST_UPLOAD_ID, 'alt' => 'Alt text']);

    $registry->validate('image', []);
})->throws(InvalidBlockPayloadException::class);

test('gallery block validator accepts and rejects payloads', function () {
    $registry = new BlockRegistry();

    $registry->validate('gallery', ['upload_ids' => [PAGE_TEST_UPLOAD_ID], 'columns' => 3]);

    $registry->validate('gallery', ['upload_ids' => []]);
})->throws(InvalidBlockPayloadException::class);

test('gallery block validator rejects invalid columns', function () {
    (new BlockRegistry())->validate('gallery', [
        'upload_ids' => [PAGE_TEST_UPLOAD_ID],
        'columns' => 9,
    ]);
})->throws(InvalidBlockPayloadException::class);

test('featured projects block validator accepts optional payload', function () {
    $registry = new BlockRegistry();

    $registry->validate('featured_projects', [
        'title' => 'Featured',
        'project_ids' => ['a0000001-0000-4000-8000-000000000020'],
        'layout' => 'grid-2',
    ]);
    $registry->validate('featured_projects', []);

    expect(true)->toBeTrue();
});

test('project list block validator accepts and rejects limit', function () {
    $registry = new BlockRegistry();

    $registry->validate('project_list', ['limit' => 12, 'layout' => 'list']);

    $registry->validate('project_list', ['limit' => 0]);
})->throws(InvalidBlockPayloadException::class);

test('tech stack block validator accepts technology ids', function () {
    $registry = new BlockRegistry();

    $registry->validate('tech_stack', [
        'technology_ids' => ['a0000001-0000-4000-8000-000000000050'],
    ]);

    expect(true)->toBeTrue();
});

test('cta block validator accepts and rejects payloads', function () {
    $registry = new BlockRegistry();

    $registry->validate('cta', ['label' => 'Go', 'href' => 'https://example.com', 'variant' => 'primary']);

    $registry->validate('cta', ['label' => 'Go']);
})->throws(InvalidBlockPayloadException::class);

test('contact form block validator accepts and rejects payloads', function () {
    $registry = new BlockRegistry();

    $registry->validate('contact_form', [
        'submit_label' => 'Send',
        'show_subject' => true,
    ]);

    $registry->validate('contact_form', []);
})->throws(InvalidBlockPayloadException::class);

test('embed block validator accepts and rejects payloads', function () {
    $registry = new BlockRegistry();

    $registry->validate('embed', [
        'provider' => 'youtube',
        'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'aspect_ratio' => '16:9',
    ]);

    $registry->validate('embed', ['provider' => 'youtube', 'url' => 'not-a-url']);
})->throws(InvalidBlockPayloadException::class);

test('spacer block validator accepts empty payload and rejects invalid size', function () {
    $registry = new BlockRegistry();

    $registry->validate('spacer', []);
    $registry->validate('spacer', ['size' => 'lg']);

    $registry->validate('spacer', ['size' => 'xl']);
})->throws(InvalidBlockPayloadException::class);

test('block registry rejects unknown type', function () {
    (new BlockRegistry())->get('carousel');
})->throws(UnknownBlockTypeException::class);
