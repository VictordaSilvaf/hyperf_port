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

namespace App\Domain\Contact\Exception;

use App\Domain\Shared\DomainException;

final class ContactRateLimitedException extends DomainException
{
    public static function exceeded(): self
    {
        return new self('Contact form rate limit exceeded.');
    }
}
