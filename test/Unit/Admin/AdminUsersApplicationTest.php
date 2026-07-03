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
require_once __DIR__ . '/../User/UserTestSupport.php';

use App\Application\User\ListUsers\ListUsersQuery;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\UpdateUser\UpdateUserCommand;
use App\Application\User\UpdateUser\UpdateUserHandler;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\ValueObject\UserId;

test('list users pagination and search', function () {
    $fixtures = userFixtures();
    $empty = $fixtures['list']->handle(new ListUsersQuery(1, 10, null));
    expect($empty['meta']['total'])->toBe(0);

    $fixtures['register']->handle(new RegisterUserCommand('Alice', 'alice@example.com', 'Secret1a'));
    $fixtures['register']->handle(new RegisterUserCommand('Bob', 'bob@example.com', 'Secret1b'));

    $page = $fixtures['list']->handle(new ListUsersQuery(1, 1, null));
    expect($page['meta']['total'])->toBe(2)
        ->and($page['meta']['per_page'])->toBe(1)
        ->and($page['data'])->toHaveCount(1);

    $search = $fixtures['list']->handle(new ListUsersQuery(1, 10, 'bob'));
    expect($search['meta']['total'])->toBe(1)
        ->and($search['data'][0]['email'])->toBe('bob@example.com');
});

test('update user profile and email conflict', function () {
    $fixtures = userFixtures();
    $a = $fixtures['register']->handle(new RegisterUserCommand('A', 'a@example.com', 'Secret1a'));
    $fixtures['register']->handle(new RegisterUserCommand('B', 'b@example.com', 'Secret1b'));

    $update = new UpdateUserHandler($fixtures['repo']);
    $update->handle(new UpdateUserCommand($a, 'Alice X', 'alice@example.com'));

    $u = $fixtures['repo']->findById(UserId::fromString($a));
    expect($u->name())->toBe('Alice X')
        ->and($u->email()->value())->toBe('alice@example.com');

    expect(fn () => $update->handle(new UpdateUserCommand($a, 'A', 'b@example.com')))
        ->toThrow(EmailAlreadyRegisteredException::class);

    expect(fn () => $update->handle(new UpdateUserCommand('00000000-0000-4000-8000-000000000099', 'X', 'x@example.com')))
        ->toThrow(UserNotFoundException::class);
});
