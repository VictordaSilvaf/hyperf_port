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

use App\Application\Project\FlushProjectViews\FlushProjectViewsHandler;
use App\Application\Project\GetProjectBySlug\GetProjectBySlugHandler;

test('public access with track view increments pending counter', function () {
    $fixtures = projectFixtures();
    $id = seedPublishedProject($fixtures, projectCreateCommand('Viewed', 'viewed-project'));

    $getBySlug = new GetProjectBySlugHandler(
        $fixtures['repo'],
        $fixtures['presenter'],
        $fixtures['cache'],
        $fixtures['viewCounter'],
    );

    $getBySlug->handle('viewed-project', trackView: true);
    $getBySlug->handle('viewed-project', trackView: true);
    $getBySlug->handle('viewed-project', trackView: true);

    expect(projectViews($fixtures, $id))->toBe(0);

    $flushed = (new FlushProjectViewsHandler($fixtures['viewCounter'], $fixtures['repo']))->handle();
    expect($flushed)->toBe(1);
    expect(projectViews($fixtures, $id))->toBe(3);
});

test('public access without track view does not increment views', function () {
    $fixtures = projectFixtures();
    $id = seedPublishedProject($fixtures, projectCreateCommand('Read Only', 'read-only'));

    $getBySlug = new GetProjectBySlugHandler(
        $fixtures['repo'],
        $fixtures['presenter'],
        $fixtures['cache'],
        $fixtures['viewCounter'],
    );

    $getBySlug->handle('read-only', trackView: false);
    $getBySlug->handle('read-only', trackView: false);

    (new FlushProjectViewsHandler($fixtures['viewCounter'], $fixtures['repo']))->handle();

    expect(projectViews($fixtures, $id))->toBe(0);
});

test('flush persists accumulated views from multiple projects', function () {
    $fixtures = projectFixtures();
    $firstId = seedPublishedProject($fixtures, projectCreateCommand('First Views', 'first-views'));
    $secondId = seedPublishedProject($fixtures, projectCreateCommand('Second Views', 'second-views'));

    $getBySlug = new GetProjectBySlugHandler(
        $fixtures['repo'],
        $fixtures['presenter'],
        $fixtures['cache'],
        $fixtures['viewCounter'],
    );

    $getBySlug->handle('first-views', trackView: true);
    $getBySlug->handle('first-views', trackView: true);
    $getBySlug->handle('second-views', trackView: true);

    (new FlushProjectViewsHandler($fixtures['viewCounter'], $fixtures['repo']))->handle();

    expect(projectViews($fixtures, $firstId))->toBe(2);
    expect(projectViews($fixtures, $secondId))->toBe(1);
});
