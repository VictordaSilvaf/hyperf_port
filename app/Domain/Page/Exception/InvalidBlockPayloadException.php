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

namespace App\Domain\Page\Exception;

use App\Domain\Shared\DomainException;

final class InvalidBlockPayloadException extends DomainException
{
    public static function forType(string $type, string $reason): self
    {
        return new self(sprintf('Invalid payload for block type "%s": %s', $type, $reason));
    }
}
