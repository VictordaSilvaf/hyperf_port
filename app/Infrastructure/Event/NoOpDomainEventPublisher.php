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

namespace App\Infrastructure\Event;

use App\Domain\Shared\Event\DomainEventInterface;
use App\Domain\Shared\Event\DomainEventPublisherInterface;

final class NoOpDomainEventPublisher implements DomainEventPublisherInterface
{
    public function publish(DomainEventInterface $event): void
    {
    }
}
