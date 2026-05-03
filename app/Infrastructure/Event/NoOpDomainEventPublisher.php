<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\Shared\Event\DomainEventInterface;
use App\Domain\Shared\Event\DomainEventPublisherInterface;

final class NoOpDomainEventPublisher implements DomainEventPublisherInterface
{
    public function publish(DomainEventInterface $event): void
    {
    }
}
