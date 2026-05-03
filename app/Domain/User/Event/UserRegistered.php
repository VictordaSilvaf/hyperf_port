<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\Shared\Event\DomainEventInterface;
use App\Domain\User\ValueObject\UserId;
use DateTimeImmutable;

final class UserRegistered implements DomainEventInterface
{
    public function __construct(
        private readonly UserId $userId,
        private readonly DateTimeImmutable $occurredOn,
    ) {
    }

    public static function forUser(UserId $userId): self
    {
        return new self($userId, new DateTimeImmutable());
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
