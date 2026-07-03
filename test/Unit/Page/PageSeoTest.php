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

use App\Domain\Page\ValueObject\PageSeo;

test('page seo from null or empty returns null', function () {
    expect(PageSeo::fromArray(null))->toBeNull();
    expect(PageSeo::fromArray([]))->toBeNull();
});

test('page seo normalizes trimmed strings and omits empty values', function () {
    $seo = PageSeo::fromArray([
        'meta_title' => '  Custom Title  ',
        'meta_description' => '  Short description  ',
        'og_title' => '',
        'canonical_url' => '   ',
    ]);

    expect($seo?->metaTitle())->toBe('Custom Title');
    expect($seo?->metaDescription())->toBe('Short description');
    expect($seo?->ogTitle())->toBeNull();
    expect($seo?->canonicalUrl())->toBeNull();
    expect($seo?->toArray())->toMatchArray([
        'meta_title' => 'Custom Title',
        'meta_description' => 'Short description',
        'robots' => 'index,follow',
        'twitter_card' => 'summary_large_image',
    ]);
});

test('page seo applies default robots and twitter card', function () {
    $seo = PageSeo::fromArray(['meta_title' => 'Title']);

    expect($seo?->robots())->toBe('index,follow');
    expect($seo?->twitterCard())->toBe('summary_large_image');
});

test('page seo accepts valid optional fields', function () {
    $seo = PageSeo::fromArray([
        'meta_title' => 'Title',
        'og_title' => 'OG Title',
        'og_description' => 'OG Description',
        'og_image_id' => PAGE_TEST_UPLOAD_ID,
        'canonical_url' => 'https://example.com/about',
        'robots' => 'noindex,nofollow',
        'twitter_card' => 'summary',
    ]);

    expect($seo?->ogTitle())->toBe('OG Title');
    expect($seo?->ogDescription())->toBe('OG Description');
    expect($seo?->ogImageId())->toBe(PAGE_TEST_UPLOAD_ID);
    expect($seo?->canonicalUrl())->toBe('https://example.com/about');
    expect($seo?->robots())->toBe('noindex,nofollow');
    expect($seo?->twitterCard())->toBe('summary');
});

test('page seo rejects meta_title longer than 70 characters', function () {
    PageSeo::fromArray(['meta_title' => str_repeat('a', 71)]);
})->throws(InvalidArgumentException::class, 'meta_title must be at most 70 characters.');

test('page seo rejects meta_description longer than 160 characters', function () {
    PageSeo::fromArray(['meta_description' => str_repeat('a', 161)]);
})->throws(InvalidArgumentException::class, 'meta_description must be at most 160 characters.');

test('page seo rejects og_title longer than 70 characters', function () {
    PageSeo::fromArray(['og_title' => str_repeat('a', 71)]);
})->throws(InvalidArgumentException::class, 'og_title must be at most 70 characters.');

test('page seo rejects og_description longer than 200 characters', function () {
    PageSeo::fromArray(['og_description' => str_repeat('a', 201)]);
})->throws(InvalidArgumentException::class, 'og_description must be at most 200 characters.');

test('page seo rejects invalid canonical url', function () {
    PageSeo::fromArray(['canonical_url' => 'not-a-url']);
})->throws(InvalidArgumentException::class, 'canonical_url must be a valid URL.');

test('page seo rejects invalid og_image_id', function () {
    PageSeo::fromArray(['og_image_id' => 'invalid-id']);
})->throws(InvalidArgumentException::class);

test('page seo rejects invalid robots value', function () {
    PageSeo::fromArray(['robots' => 'index,nope']);
})->throws(InvalidArgumentException::class, 'Invalid robots value.');

test('page seo rejects invalid twitter_card value', function () {
    PageSeo::fromArray(['twitter_card' => 'player']);
})->throws(InvalidArgumentException::class, 'Invalid twitter_card value.');
