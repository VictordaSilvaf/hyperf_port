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
use App\Application\Auth\ChangePassword\ChangePasswordCommand;
use App\Application\Auth\ChangePassword\ChangePasswordHandler;
use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\LoginUser\LoginUserCommand;
use App\Application\Auth\LoginUser\LoginUserHandler;
use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Application\Auth\ResetPassword\ResetPasswordCommand;
use App\Application\Auth\ResetPassword\ResetPasswordHandler;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Infrastructure\Auth\SignedAccessTokenIssuer;
use App\Infrastructure\Cache\ArrayPasswordResetTokenStore;
use App\Infrastructure\Event\NoOpDomainEventPublisher;
use App\Infrastructure\Persistence\Acl\InMemoryAclStore;
use App\Infrastructure\Persistence\Acl\InMemoryEffectivePermissionsProvider;
use App\Infrastructure\Persistence\Acl\InMemoryRoleRepository;
use App\Infrastructure\Persistence\Acl\InMemoryUserRoleRepository;
use App\Infrastructure\Persistence\User\InMemoryUserRepository;
use App\Infrastructure\Security\NativePasswordHasher;

beforeEach(function () {
    putenv('APP_AUTH_SECRET=test-secret-at-least-thirty-two-characters-long');
    putenv('APP_AUTH_TOKEN_TTL=3600');
});

test('register login and password reset flow', function () {
    $repo = new InMemoryUserRepository();
    $hasher = new NativePasswordHasher();
    $events = new NoOpDomainEventPublisher();
    $acl = InMemoryAclStore::seeded();
    $roleRepo = new InMemoryRoleRepository($acl);
    $userRoles = new InMemoryUserRoleRepository($acl, $roleRepo);
    $effective = new InMemoryEffectivePermissionsProvider($acl);
    $register = new RegisterUserHandler($repo, $hasher, $events, $userRoles);
    $tokens = new SignedAccessTokenIssuer();
    $login = new LoginUserHandler($repo, $hasher, $tokens, $effective);
    $store = new ArrayPasswordResetTokenStore();
    $reset = new ResetPasswordHandler($store, $repo, $hasher);

    $userId = $register->handle(new RegisterUserCommand('Jane', 'jane@example.com', 'Secret1a'));

    expect($userId)->toMatch('/^[0-9a-f-]{36}$/');

    $result = $login->handle(new LoginUserCommand('jane@example.com', 'Secret1a'));
    expect($result->accessToken)->toContain('.');

    expect(fn () => $login->handle(new LoginUserCommand('jane@example.com', 'WrongPass1')))->toThrow(InvalidCredentialsException::class);

    $code = $store->issue($userId);
    expect($code)->toMatch('/^\d{6}$/');
    $reset->handle(new ResetPasswordCommand($code, 'NewSecret1b'));

    $login->handle(new LoginUserCommand('jane@example.com', 'NewSecret1b'));
});

test('register rejects duplicate email', function () {
    $repo = new InMemoryUserRepository();
    $acl = InMemoryAclStore::seeded();
    $userRoles = new InMemoryUserRoleRepository($acl, new InMemoryRoleRepository($acl));
    $register = new RegisterUserHandler($repo, new NativePasswordHasher(), new NoOpDomainEventPublisher(), $userRoles);

    $register->handle(new RegisterUserCommand('A', 'dup@example.com', 'Secret1a'));

    expect(fn () => $register->handle(new RegisterUserCommand('B', 'dup@example.com', 'Secret1a')))
        ->toThrow(EmailAlreadyRegisteredException::class);
});

test('change password requires current password', function () {
    $repo = new InMemoryUserRepository();
    $hasher = new NativePasswordHasher();
    $events = new NoOpDomainEventPublisher();
    $acl = InMemoryAclStore::seeded();
    $roleRepo = new InMemoryRoleRepository($acl);
    $userRoles = new InMemoryUserRoleRepository($acl, $roleRepo);
    $effective = new InMemoryEffectivePermissionsProvider($acl);
    $register = new RegisterUserHandler($repo, $hasher, $events, $userRoles);
    $userId = $register->handle(new RegisterUserCommand('Z', 'z@example.com', 'Secret1a'));

    $change = new ChangePasswordHandler($repo, $hasher);

    expect(fn () => $change->handle(new ChangePasswordCommand(
        $userId,
        'wrong',
        'OtherSecret1b',
    )))->toThrow(InvalidCredentialsException::class);

    $change->handle(new ChangePasswordCommand(
        $userId,
        'Secret1a',
        'OtherSecret1b',
    ));

    $tokens = new SignedAccessTokenIssuer();
    $login = new LoginUserHandler($repo, $hasher, $tokens, $effective);
    $login->handle(new LoginUserCommand('z@example.com', 'OtherSecret1b'));
});

test('reset password rejects invalid code', function () {
    $repo = new InMemoryUserRepository();

    $store = new class implements PasswordResetTokenStoreInterface {
        public function issue(string $userId): string
        {
            return 'dummy';
        }

        public function consume(string $token): ?string
        {
            return null;
        }
    };

    $reset = new ResetPasswordHandler($store, $repo, new NativePasswordHasher());

    expect(fn () => $reset->handle(new ResetPasswordCommand('000000', 'Secret1b')))
        ->toThrow(InvalidCredentialsException::class);
});
