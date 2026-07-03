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

use App\Application\Project\GetProject\GetProjectHandler;
use App\Application\Project\ManageProjectImages\AddProjectImageHandler;
use App\Application\Project\ManageProjectImages\RemoveProjectImageHandler;
use App\Application\Project\ManageProjectImages\ReorderProjectImagesHandler;
use App\Application\Project\ManageProjectImages\SetProjectThumbnailHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\ValueObject\ProjectId;

test('add remove and reorder project images', function () {
    $fixtures = projectFixtures();
    $projectId = seedProject($fixtures);
    $firstUpload = seedUpload($fixtures, 'uploads/a.jpg');
    $secondUpload = seedUpload($fixtures, 'uploads/b.jpg');

    $add = new AddProjectImageHandler($fixtures['repo'], $fixtures['uploads'], $fixtures['cache']);
    $remove = new RemoveProjectImageHandler($fixtures['repo'], $fixtures['cache']);
    $reorder = new ReorderProjectImagesHandler($fixtures['repo'], $fixtures['cache']);

    $first = $add->handle($projectId, $firstUpload->id()->value(), 'First');
    $second = $add->handle($projectId, $secondUpload->id()->value(), 'Second');

    $images = $fixtures['repo']->imagesFor(ProjectId::fromString($projectId));
    expect($images)->toHaveCount(2);

    $reorder->handle($projectId, [
        ['id' => $second['id'], 'order' => 1],
        ['id' => $first['id'], 'order' => 2],
    ]);

    $reordered = $fixtures['repo']->imagesFor(ProjectId::fromString($projectId));
    usort($reordered, static fn ($a, $b) => $a->sortOrder() <=> $b->sortOrder());
    expect($reordered[0]->id()->value())->toBe($second['id']);
    expect($reordered[1]->id()->value())->toBe($first['id']);

    $remove->handle($projectId, $first['id']);
    expect($fixtures['repo']->imagesFor(ProjectId::fromString($projectId)))->toHaveCount(1);
});

test('set thumbnail and cover from upload', function () {
    $fixtures = projectFixtures();
    $projectId = seedProject($fixtures);
    $thumb = seedUpload($fixtures, 'uploads/thumb.jpg');
    $cover = seedUpload($fixtures, 'uploads/cover.jpg');

    $set = new SetProjectThumbnailHandler($fixtures['repo'], $fixtures['uploads'], $fixtures['cache']);
    $set->handle($projectId, $thumb->id()->value());
    $set->setCover($projectId, $cover->id()->value());

    $detail = (new GetProjectHandler($fixtures['repo'], $fixtures['presenter']))->handle($projectId);

    expect($detail['data']['thumbnail'])->toBe('uploads/thumb.jpg');
    expect($detail['data']['cover'])->toBe('uploads/cover.jpg');
});

test('add image rejects missing upload', function () {
    $fixtures = projectFixtures();
    $projectId = seedProject($fixtures);

    $add = new AddProjectImageHandler($fixtures['repo'], $fixtures['uploads'], $fixtures['cache']);
    $add->handle($projectId, 'a0000001-0000-4000-8000-000000000099', null);
})->throws(ProjectNotFoundException::class);
