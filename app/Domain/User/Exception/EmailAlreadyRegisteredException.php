<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\DomainException;

final class EmailAlreadyRegisteredException extends DomainException
{
    public static function forEmail(string $email): self
    {
        return new self(sprintf('An account with email %s already exists.', $email));
    }
}
