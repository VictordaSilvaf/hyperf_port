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
use App\Application\Project\GetProject\GetProjectQuery;
use App\Application\Project\PublishProject\PublishProjectCommand;
use App\Application\Project\PublishProject\PublishProjectHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Infrastructure\Persistence\Project\InMemoryProjectRepository;

test('create publish and fetch project', function () {
    $repo = new InMemoryProjectRepository();
    $create = new CreateProjectHandler($repo);
    $publish = new PublishProjectHandler($repo);
    $get = new GetProjectHandler($repo);

    $id = $create->handle(new CreateProjectCommand('Portfolio', null, 'My work', null, null));
    $publish->handle(new PublishProjectCommand($id));

    $result = $get->handle(new GetProjectQuery($id));
    expect($result->status)->toBe(ProjectStatus::Published->value);
    expect($result->slug)->toBe('portfolio');
});

test('get project throws when missing', function () {
    $get = new GetProjectHandler(new InMemoryProjectRepository());
    $get->handle(new GetProjectQuery('a0000001-0000-4000-8000-000000000001'));
})->throws(ProjectNotFoundException::class);
