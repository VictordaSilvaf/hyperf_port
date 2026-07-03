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

use App\Application\Project\SyncProjectTaxonomies\SyncProjectCategoriesHandler;
use App\Application\Project\SyncProjectTaxonomies\SyncProjectTagsHandler;
use App\Application\Project\SyncProjectTaxonomies\SyncProjectTechnologiesHandler;
use App\Domain\Project\ValueObject\ProjectId;

test('sync categories technologies and tags on project', function () {
    $fixtures = projectFixtures();
    $projectId = seedProject($fixtures);

    $categories = new SyncProjectCategoriesHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $technologies = new SyncProjectTechnologiesHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $tags = new SyncProjectTagsHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);

    $categories->handle($projectId, ['cat-1', 'cat-2']);
    $technologies->handle($projectId, ['tech-1']);
    $tags->handle($projectId, ['tag-1', 'tag-2', 'tag-3']);

    $id = ProjectId::fromString($projectId);
    expect($fixtures['repo']->categoryIdsFor($id))->toBe(['cat-1', 'cat-2']);
    expect($fixtures['repo']->technologyIdsFor($id))->toBe(['tech-1']);
    expect($fixtures['repo']->tagIdsFor($id))->toBe(['tag-1', 'tag-2', 'tag-3']);
});

test('sync taxonomies replaces previous relations', function () {
    $fixtures = projectFixtures();
    $projectId = seedProject($fixtures);
    $id = ProjectId::fromString($projectId);

    $categories = new SyncProjectCategoriesHandler($fixtures['repo'], $fixtures['cache'], $fixtures['cacheInvalidator'], $fixtures['presenter']);
    $categories->handle($projectId, ['cat-old']);
    $categories->handle($projectId, ['cat-new']);

    expect($fixtures['repo']->categoryIdsFor($id))->toBe(['cat-new']);
});
