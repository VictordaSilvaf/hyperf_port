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

namespace App\Domain\User\Exception;

use App\Domain\Shared\DomainException;

final class EmailAlreadyRegisteredException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $email,
    ) {
        parent::__construct($message);
    }

    public static function forEmail(string $email): self
    {
        return new self(sprintf('An account with email %s already exists.', $email), $email);
    }
}
