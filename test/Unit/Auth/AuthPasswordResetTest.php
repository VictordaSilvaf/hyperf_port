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
use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;
use App\Application\Auth\RefreshAccessToken\RefreshAccessTokenHandler;
use App\Application\Auth\RequestPasswordReset\RequestPasswordResetCommand;
use App\Application\Auth\RequestPasswordReset\RequestPasswordResetHandler;
use App\Application\Auth\ResetPassword\ResetPasswordCommand;
use App\Application\Auth\ResetPassword\ResetPasswordHandler;
use App\Infrastructure\Cache\ArrayPasswordResetTokenStore;

test('refresh access token for existing user', function () {
    $fixtures = authFixtures();
    $userId = registerTestUser($fixtures);

    $refresh = new RefreshAccessTokenHandler($fixtures['repo'], $fixtures['tokens']);
    $token = $refresh->handle($userId);

    expect($token)->toContain('.');
    expect($fixtures['tokens']->parseUserId($token))->toBe($userId);
});

test('refresh access token rejects missing user', function () {
    $fixtures = authFixtures();
    $refresh = new RefreshAccessTokenHandler($fixtures['repo'], $fixtures['tokens']);

    $refresh->handle('a0000001-0000-4000-8000-000000000001');
})->throws(InvalidCredentialsException::class);

test('request password reset notifies only existing users', function () {
    $fixtures = authFixtures();
    $userId = registerTestUser($fixtures, 'Reset Me', 'reset@example.com');
    $store = new ArrayPasswordResetTokenStore();
    $notified = [];

    $notifier = new class($notified) implements PasswordResetNotifierInterface {
        public function __construct(private array &$notified)
        {
        }

        public function notify(string $email, string $plainToken): void
        {
            $this->notified = ['email' => $email, 'token' => $plainToken];
        }
    };

    $request = new RequestPasswordResetHandler($fixtures['repo'], $store, $notifier);
    $request->handle(new RequestPasswordResetCommand('unknown@example.com'));
    expect($notified)->toBe([]);

    $request->handle(new RequestPasswordResetCommand('reset@example.com'));
    expect($notified['email'])->toBe('reset@example.com');
    expect($notified['token'])->toMatch('/^\d{6}$/');
    expect($store->consume($notified['token']))->toBe($userId);
});

test('reset password token is single use', function () {
    $fixtures = authFixtures();
    $userId = registerTestUser($fixtures, 'Once', 'once@example.com');
    $store = new ArrayPasswordResetTokenStore();
    $code = $store->issue($userId);

    $reset = new ResetPasswordHandler($store, $fixtures['repo'], $fixtures['hasher']);
    $reset->handle(new ResetPasswordCommand($code, 'NewSecret1b'));

    $fixtures['login']->handle(new LoginUserCommand('once@example.com', 'NewSecret1b'));

    expect(fn () => $reset->handle(new ResetPasswordCommand($code, 'AnotherSecret1')))
        ->toThrow(InvalidCredentialsException::class);
});

test('change password rejects missing user', function () {
    $fixtures = authFixtures();
    $change = new ChangePasswordHandler($fixtures['repo'], $fixtures['hasher']);

    $change->handle(new ChangePasswordCommand(
        'a0000001-0000-4000-8000-000000000001',
        'Secret1a',
        'OtherSecret1b',
    ));
})->throws(InvalidCredentialsException::class);
