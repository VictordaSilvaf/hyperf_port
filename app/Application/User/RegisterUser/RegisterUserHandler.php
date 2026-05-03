<?php

declare(strict_types=1);

namespace App\Application\User\RegisterUser;

use App\Application\Shared\Security\PasswordHasherInterface;
use App\Domain\Shared\Event\DomainEventPublisherInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Event\UserRegistered;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;

final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly DomainEventPublisherInterface $domainEvents,
    ) {
    }

    public function handle(RegisterUserCommand $command): string
    {
        $email = Email::fromString($command->email);
        if ($this->users->findByEmail($email) !== null) {
            throw EmailAlreadyRegisteredException::forEmail($email->value());
        }

        $hash = $this->passwordHasher->hash($command->password);
        $user = User::register($command->name, $email, $hash);
        $this->users->save($user);
        $this->domainEvents->publish(UserRegistered::forUser($user->id()));

        return $user->id()->value();
    }
}
