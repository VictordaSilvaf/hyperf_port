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

use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\LoginUser\LoginUserCommand;

test('login rejects unknown email', function () {
    $fixtures = authFixtures();

    expect(fn () => $fixtures['login']->handle(new LoginUserCommand('ghost@example.com', 'Secret1a')))
        ->toThrow(InvalidCredentialsException::class);
});

test('login returns user role and permissions after register', function () {
    $fixtures = authFixtures();
    registerTestUser($fixtures, 'Admin Candidate', 'admin@example.com');

    $result = $fixtures['login']->handle(new LoginUserCommand('admin@example.com', 'Secret1a'));

    expect($result->accessToken)->toContain('.');
    expect($result->roleSlugs)->toContain('user');
    expect($result->permissionSlugs)->toBeArray();
});

test('login rejects wrong password for existing user', function () {
    $fixtures = authFixtures();
    registerTestUser($fixtures);

    expect(fn () => $fixtures['login']->handle(new LoginUserCommand('jane@example.com', 'WrongPass1')))
        ->toThrow(InvalidCredentialsException::class);
});

test('issued access token encodes registered user id', function () {
    $fixtures = authFixtures();
    $userId = registerTestUser($fixtures);

    $token = $fixtures['login']->handle(new LoginUserCommand('jane@example.com', 'Secret1a'))->accessToken;

    expect($fixtures['tokens']->parseUserId($token))->toBe($userId);
});
