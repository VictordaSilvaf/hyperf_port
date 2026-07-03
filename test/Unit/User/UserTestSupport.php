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
use App\Application\User\ListUsers\ListUsersHandler;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Persistence\Acl\InMemoryAclStore;
use App\Infrastructure\Persistence\Acl\InMemoryRoleRepository;
use App\Infrastructure\Persistence\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;

require_once __DIR__ . '/../Auth/AuthTestSupport.php';

/**
 * @return array{
 *     repo: InMemoryUserRepository,
 *     list: ListUsersHandler,
 *     register: RegisterUserHandler,
 *     acl: InMemoryAclStore,
 *     userRoles: InMemoryUserRoleRepository,
 * }
 */
function userFixtures(): array
{
    $repo = new InMemoryUserRepository();
    $acl = InMemoryAclStore::seeded();
    $userRoles = new InMemoryUserRoleRepository($acl, new InMemoryRoleRepository($acl));
    $register = new RegisterUserHandler($repo, new NativePasswordHasher(), new NoOpDomainEventPublisher(), $userRoles);

    return [
        'repo' => $repo,
        'list' => new ListUsersHandler($repo),
        'register' => $register,
        'acl' => $acl,
        'userRoles' => $userRoles,
    ];
}

function seedUsers(array $fixtures, int $count = 2): void
{
    for ($i = 1; $i <= $count; ++$i) {
        $fixtures['register']->handle(new RegisterUserCommand(
            'User ' . $i,
            'user' . $i . '@example.com',
            'Secret1a',
        ));
    }
}
