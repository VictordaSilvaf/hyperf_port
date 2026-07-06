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
use App\Application\Site\GetSiteSettings\GetSiteSettingsHandler;
use App\Application\Site\UpdateSiteSettings\UpdateSiteSettingsCommand;
use App\Application\Site\UpdateSiteSettings\UpdateSiteSettingsHandler;
use App\Domain\Site\Entity\SiteSettings;
use App\Infrastructure\Cache\ArraySitePublicCache;
use App\Infrastructure\Persistence\Site\InMemorySiteSettingsRepository;

function siteSettingsFixtures(): array
{
    $settings = new InMemorySiteSettingsRepository();
    $cache = new ArraySitePublicCache();
    $get = new GetSiteSettingsHandler($settings, $cache);
    $update = new UpdateSiteSettingsHandler($settings, $cache);

    return compact('settings', 'cache', 'get', 'update');
}

test('get site settings returns singleton defaults', function () {
    $fixtures = siteSettingsFixtures();

    $result = $fixtures['get']->handle();

    expect($result['data']['seo']['site_name'])->toBe('Victor Dev');
    expect($result['data']['seo']['locale'])->toBe('pt_BR');
    expect($result['data']['nav'])->toBeNull();
    expect($result['data']['updated_at'])->toBeNull();
});

test('update site settings persists changes on singleton', function () {
    $fixtures = siteSettingsFixtures();

    $updated = $fixtures['update']->handle(new UpdateSiteSettingsCommand([
        'nav' => ['items' => [['label' => 'Home', 'href' => '/']]],
        'branding' => ['logo_text' => 'Victor'],
        'seo' => [
            'site_name' => 'Victor Portfolio',
            'default_meta_description' => 'Developer portfolio',
            'locale' => 'en',
        ],
    ]));

    expect($updated['data']['nav'])->toBe(['items' => [['label' => 'Home', 'href' => '/']]]);
    expect($updated['data']['branding'])->toBe(['logo_text' => 'Victor']);
    expect($updated['data']['seo']['site_name'])->toBe('Victor Portfolio');
    expect($updated['data']['seo']['default_meta_description'])->toBe('Developer portfolio');
    expect($updated['data']['seo']['locale'])->toBe('en');
    expect($updated['data']['updated_at'])->not->toBeNull();

    $stored = $fixtures['settings']->get();
    expect($stored->id())->toBe(SiteSettings::SINGLETON_ID);
    expect($stored->seo()->siteName())->toBe('Victor Portfolio');
});

test('get site settings uses cache on repeated reads', function () {
    $fixtures = siteSettingsFixtures();

    $first = $fixtures['get']->handle();
    $cached = $fixtures['get']->handle();

    expect($cached)->toBe($first);
});

test('update site settings bumps cache', function () {
    $fixtures = siteSettingsFixtures();

    $fixtures['get']->handle();
    $fixtures['update']->handle(new UpdateSiteSettingsCommand([
        'social' => ['github' => 'https://github.com/example'],
    ]));
    $fresh = $fixtures['get']->handle();

    expect($fresh['data']['social'])->toBe(['github' => 'https://github.com/example']);
    expect($fresh['data']['updated_at'])->not->toBeNull();
});

test('partial update keeps untouched fields', function () {
    $fixtures = siteSettingsFixtures();

    $fixtures['update']->handle(new UpdateSiteSettingsCommand([
        'footer' => ['text' => '© 2026'],
        'seo' => ['site_name' => 'My Site'],
    ]));

    $result = $fixtures['update']->handle(new UpdateSiteSettingsCommand([
        'social' => ['linkedin' => 'https://linkedin.com/in/example'],
    ]));

    expect($result['data']['footer'])->toBe(['text' => '© 2026']);
    expect($result['data']['seo']['site_name'])->toBe('My Site');
    expect($result['data']['social'])->toBe(['linkedin' => 'https://linkedin.com/in/example']);
});

test('update site settings persists contact info', function () {
    $fixtures = siteSettingsFixtures();

    $result = $fixtures['update']->handle(new UpdateSiteSettingsCommand([
        'contact' => [
            'email' => 'hello@victordev.com',
            'phone' => '+351 900 000 000',
            'notification_email' => 'admin@victordev.com',
        ],
    ]));

    expect($result['data']['contact']['email'])->toBe('hello@victordev.com');
    expect($result['data']['contact']['notification_email'])->toBe('admin@victordev.com');

    $fresh = $fixtures['get']->handle();
    expect($fresh['data']['contact']['phone'])->toBe('+351 900 000 000');
});
