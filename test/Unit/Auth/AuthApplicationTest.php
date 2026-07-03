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
require_once __DIR__ . '/AuthTestSupport.php';

use App\Application\Auth\ChangePassword\ChangePasswordCommand;
use App\Application\Auth\ChangePassword\ChangePasswordHandler;
use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\LoginUser\LoginUserCommand;
use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Application\Auth\ResetPassword\ResetPasswordCommand;
use App\Application\Auth\ResetPassword\ResetPasswordHandler;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Infrastructure\Cache\ArrayPasswordResetTokenStore;

test('register login and password reset flow', function () {
    $fixtures = authFixtures();
    $store = new ArrayPasswordResetTokenStore();
    $reset = new ResetPasswordHandler($store, $fixtures['repo'], $fixtures['hasher']);

    $userId = $fixtures['register']->handle(new RegisterUserCommand('Jane', 'jane@example.com', 'Secret1a'));

    expect($userId)->toMatch('/^[0-9a-f-]{36}$/');

    $result = $fixtures['login']->handle(new LoginUserCommand('jane@example.com', 'Secret1a'));
    expect($result->accessToken)->toContain('.');

    expect(fn () => $fixtures['login']->handle(new LoginUserCommand('jane@example.com', 'WrongPass1')))
        ->toThrow(InvalidCredentialsException::class);

    $code = $store->issue($userId);
    expect($code)->toMatch('/^\d{6}$/');
    $reset->handle(new ResetPasswordCommand($code, 'NewSecret1b'));

    $fixtures['login']->handle(new LoginUserCommand('jane@example.com', 'NewSecret1b'));
});

test('register rejects duplicate email', function () {
    $fixtures = authFixtures();

    $fixtures['register']->handle(new RegisterUserCommand('A', 'dup@example.com', 'Secret1a'));

    expect(fn () => $fixtures['register']->handle(new RegisterUserCommand('B', 'dup@example.com', 'Secret1a')))
        ->toThrow(EmailAlreadyRegisteredException::class);
});

test('change password requires current password', function () {
    $fixtures = authFixtures();
    $userId = registerTestUser($fixtures, 'Z', 'z@example.com');

    $change = new ChangePasswordHandler($fixtures['repo'], $fixtures['hasher']);

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

    $fixtures['login']->handle(new LoginUserCommand('z@example.com', 'OtherSecret1b'));
});

test('reset password rejects invalid code', function () {
    $fixtures = authFixtures();

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

    $reset = new ResetPasswordHandler($store, $fixtures['repo'], $fixtures['hasher']);

    expect(fn () => $reset->handle(new ResetPasswordCommand('000000', 'Secret1b')))
        ->toThrow(InvalidCredentialsException::class);
});
