<?php

declare(strict_types=1);

namespace App\Application\Auth\LoginUser;

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
    ) {
    }

    public function handle(LoginUserCommand $command): string
    {
        $email = Email::fromString($command->email);
        $user = $this->users->findByEmail($email);
        if ($user === null || ! $this->passwordHasher->verify($command->password, $user->passwordHash())) {
            throw new InvalidCredentialsException();
        }

        return $this->accessTokens->issue($user->id()->value());
    }
}
