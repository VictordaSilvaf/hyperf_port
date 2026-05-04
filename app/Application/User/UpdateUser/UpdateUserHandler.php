<?php

declare(strict_types=1);

namespace App\Application\User\UpdateUser;

use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;

final class UpdateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function handle(UpdateUserCommand $command): void
    {
        $id = UserId::fromString($command->userId);
        $user = $this->users->findById($id);
        if ($user === null) {
            throw UserNotFoundException::byId($command->userId);
        }

        $email = Email::fromString($command->email);
        $existing = $this->users->findByEmail($email);
        if ($existing !== null && $existing->id()->value() !== $command->userId) {
            throw EmailAlreadyRegisteredException::forEmail($email->value());
        }

        $this->users->save($user->withProfile($command->name, $email));
    }
}
