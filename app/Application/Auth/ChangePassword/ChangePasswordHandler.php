<?php

declare(strict_types=1);

namespace App\Application\Auth\ChangePassword;

use App\Application\Auth\InvalidCredentialsException;
use App\Application\Shared\Security\PasswordHasherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;

final class ChangePasswordHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function handle(ChangePasswordCommand $command): void
    {
        $id = UserId::fromString($command->userId);
        $user = $this->users->findById($id);
        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        if (! $this->passwordHasher->verify($command->currentPassword, $user->passwordHash())) {
            throw new InvalidCredentialsException();
        }

        $updated = $user->changePassword($this->passwordHasher->hash($command->newPassword));
        $this->users->save($updated);
    }
}
