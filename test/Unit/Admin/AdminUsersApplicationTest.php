<?php

declare(strict_types=1);

use App\Application\User\ListUsers\ListUsersHandler;
use App\Application\User\ListUsers\ListUsersQuery;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Application\User\UpdateUser\UpdateUserCommand;
use App\Application\User\UpdateUser\UpdateUserHandler;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Infrastructure\Acl\InMemoryAclStore;
use App\Infrastructure\Acl\InMemoryRoleRepository;
use App\Infrastructure\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;

test('list users pagination and search', function () {
    $repo = new InMemoryUserRepository();
    $list = new ListUsersHandler($repo);
    $empty = $list->handle(new ListUsersQuery(1, 10, null));
    expect($empty['meta']['total'])->toBe(0);

    $acl = InMemoryAclStore::seeded();
    $userRoles = new InMemoryUserRoleRepository($acl, new InMemoryRoleRepository($acl));
    $register = new RegisterUserHandler($repo, new NativePasswordHasher(), new NoOpDomainEventPublisher(), $userRoles);
    $register->handle(new RegisterUserCommand('Alice', 'alice@example.com', 'Secret1a'));
    $register->handle(new RegisterUserCommand('Bob', 'bob@example.com', 'Secret1b'));

    $page = $list->handle(new ListUsersQuery(1, 1, null));
    expect($page['meta']['total'])->toBe(2)
        ->and($page['meta']['per_page'])->toBe(1)
        ->and($page['data'])->toHaveCount(1);

    $search = $list->handle(new ListUsersQuery(1, 10, 'bob'));
    expect($search['meta']['total'])->toBe(1)
        ->and($search['data'][0]['email'])->toBe('bob@example.com');
});

test('update user profile and email conflict', function () {
    $repo = new InMemoryUserRepository();
    $acl = InMemoryAclStore::seeded();
    $userRoles = new InMemoryUserRoleRepository($acl, new InMemoryRoleRepository($acl));
    $register = new RegisterUserHandler($repo, new NativePasswordHasher(), new NoOpDomainEventPublisher(), $userRoles);
    $a = $register->handle(new RegisterUserCommand('A', 'a@example.com', 'Secret1a'));
    $register->handle(new RegisterUserCommand('B', 'b@example.com', 'Secret1b'));

    $update = new UpdateUserHandler($repo);
    $update->handle(new UpdateUserCommand($a, 'Alice X', 'alice@example.com'));

    $u = $repo->findById(\App\Domain\User\ValueObject\UserId::fromString($a));
    expect($u->name())->toBe('Alice X')
        ->and($u->email()->value())->toBe('alice@example.com');

    expect(fn () => $update->handle(new UpdateUserCommand($a, 'A', 'b@example.com')))
        ->toThrow(EmailAlreadyRegisteredException::class);

    expect(fn () => $update->handle(new UpdateUserCommand('00000000-0000-4000-8000-000000000099', 'X', 'x@example.com')))
        ->toThrow(UserNotFoundException::class);
});
