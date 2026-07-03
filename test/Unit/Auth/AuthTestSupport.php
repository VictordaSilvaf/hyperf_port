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
use App\Application\Auth\LoginUser\LoginUserHandler;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Infrastructure\Auth\SignedAccessTokenIssuer;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Persistence\Acl\InMemoryAclStore;
use App\Infrastructure\Persistence\Acl\InMemoryEffectivePermissionsProvider;
use App\Infrastructure\Persistence\Acl\InMemoryRoleRepository;
use App\Infrastructure\Persistence\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;

/**
 * @return array{
 *     repo: InMemoryUserRepository,
 *     hasher: NativePasswordHasher,
 *     acl: InMemoryAclStore,
 *     userRoles: InMemoryUserRoleRepository,
 *     effective: InMemoryEffectivePermissionsProvider,
 *     register: RegisterUserHandler,
 *     tokens: SignedAccessTokenIssuer,
 *     login: LoginUserHandler,
 * }
 */
function authFixtures(): array
{
    putenv('APP_AUTH_SECRET=test-secret-at-least-thirty-two-characters-long');
    putenv('APP_AUTH_TOKEN_TTL=3600');

    $repo = new InMemoryUserRepository();
    $hasher = new NativePasswordHasher();
    $acl = InMemoryAclStore::seeded();
    $roleRepo = new InMemoryRoleRepository($acl);
    $userRoles = new InMemoryUserRoleRepository($acl, $roleRepo);
    $effective = new InMemoryEffectivePermissionsProvider($acl);
    $register = new RegisterUserHandler($repo, $hasher, new NoOpDomainEventPublisher(), $userRoles);
    $tokens = new SignedAccessTokenIssuer();
    $login = new LoginUserHandler($repo, $hasher, $tokens, $effective);

    return compact('repo', 'hasher', 'acl', 'userRoles', 'effective', 'register', 'tokens', 'login');
}

function registerTestUser(
    array $fixtures,
    string $name = 'Jane Doe',
    string $email = 'jane@example.com',
    string $password = 'Secret1a',
): string {
    return $fixtures['register']->handle(new RegisterUserCommand($name, $email, $password));
}
