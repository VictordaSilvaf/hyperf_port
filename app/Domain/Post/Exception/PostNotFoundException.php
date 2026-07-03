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

namespace App\Domain\Post\Exception;

use App\Domain\Shared\DomainException;

final class PostNotFoundException extends DomainException
{
    public static function byId(string $id): self
    {
        return new self(sprintf('Post not found: %s', $id));
    }
}
