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
require_once __DIR__ . '/UserTestSupport.php';

use App\Application\User\GetUser\GetUserHandler;
use App\Application\User\GetUser\GetUserQuery;
use App\Application\User\ListUsers\ListUsersQuery;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\UpdateUser\UpdateUserCommand;
use App\Application\User\UpdateUser\UpdateUserHandler;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\ValueObject\UserId;

test('get user returns profile data', function () {
    $fixtures = userFixtures();
    $userId = $fixtures['register']->handle(new RegisterUserCommand(
        'Carol',
        'carol@example.com',
        'Secret1a',
    ));

    $result = (new GetUserHandler($fixtures['repo']))->handle(new GetUserQuery($userId));

    expect($result->id)->toBe($userId);
    expect($result->name)->toBe('Carol');
    expect($result->email)->toBe('carol@example.com');
});

test('get user throws when missing', function () {
    $fixtures = userFixtures();
    $get = new GetUserHandler($fixtures['repo']);
    $get->handle(new GetUserQuery('a0000001-0000-4000-8000-000000000001'));
})->throws(UserNotFoundException::class);

test('register assigns default user role', function () {
    $fixtures = userFixtures();

    $userId = $fixtures['register']->handle(new RegisterUserCommand(
        'Role Test',
        'role@example.com',
        'Secret1a',
    ));

    expect($fixtures['userRoles']->getRoleIdsForUser($userId))->not->toBeEmpty();
});

test('list users calculates pagination meta', function () {
    $fixtures = userFixtures();
    seedUsers($fixtures, 3);

    $page = $fixtures['list']->handle(new ListUsersQuery(2, 2, null));

    expect($page['meta']['total'])->toBe(3);
    expect($page['meta']['page'])->toBe(2);
    expect($page['meta']['per_page'])->toBe(2);
    expect($page['meta']['last_page'])->toBe(2);
    expect($page['data'])->toHaveCount(1);
});

test('list users search matches name or email', function () {
    $fixtures = userFixtures();
    $fixtures['register']->handle(new RegisterUserCommand(
        'Searchable Name',
        'findme@example.com',
        'Secret1a',
    ));
    seedUsers($fixtures, 2);

    $byName = $fixtures['list']->handle(new ListUsersQuery(1, 10, 'searchable'));
    $byEmail = $fixtures['list']->handle(new ListUsersQuery(1, 10, 'findme'));

    expect($byName['meta']['total'])->toBe(1);
    expect($byEmail['meta']['total'])->toBe(1);
    expect($byEmail['data'][0]['email'])->toBe('findme@example.com');
});

test('update user allows keeping the same email', function () {
    $fixtures = userFixtures();
    $userId = $fixtures['register']->handle(new RegisterUserCommand(
        'Same Email',
        'same@example.com',
        'Secret1a',
    ));

    $update = new UpdateUserHandler($fixtures['repo']);
    $update->handle(new UpdateUserCommand($userId, 'Same Email Updated', 'same@example.com'));

    $user = $fixtures['repo']->findById(UserId::fromString($userId));
    expect($user?->name())->toBe('Same Email Updated');
    expect($user?->email()->value())->toBe('same@example.com');
});
