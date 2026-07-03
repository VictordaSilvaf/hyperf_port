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

namespace App\Application\Auth\LoginUser;

use App\Application\Acl\EffectivePermissionsProviderInterface;
use App\Application\Auth\AccessTokenIssuerInterface;
use App\Application\Auth\InvalidCredentialsException;
use App\Application\Shared\Security\PasswordHasherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;

final class LoginUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly AccessTokenIssuerInterface $accessTokens,
        private readonly EffectivePermissionsProviderInterface $effectivePermissions,
    ) {
    }

    public function handle(LoginUserCommand $command): LoginUserResult
    {
        $email = Email::fromString($command->email);
        $user = $this->users->findByEmail($email);
        if ($user === null || ! $this->passwordHasher->verify($command->password, $user->passwordHash())) {
            throw new InvalidCredentialsException();
        }

        $uid = $user->id()->value();

        return new LoginUserResult(
            $this->accessTokens->issue($uid),
            $this->effectivePermissions->roleSlugsForUser($uid),
            $this->effectivePermissions->permissionSlugsForUser($uid),
        );
    }
}
