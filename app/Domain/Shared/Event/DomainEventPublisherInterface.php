<?php

declare(strict_types=1);

namespace App\Domain\Shared\Event;

interface DomainEventPublisherInterface
{
    public function publish(DomainEventInterface $event): void;
}
