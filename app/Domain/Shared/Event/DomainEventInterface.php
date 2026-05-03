<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

use DateTimeImmutable;

interface DomainEventInterface
{
    public function occurredOn(): DateTimeImmutable;
}
