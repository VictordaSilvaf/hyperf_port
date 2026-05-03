<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\DomainException;

final class UserNotFoundException extends DomainException
{
    public static function byId(string $id): self
    {
        $e = new self(sprintf('User not found: %s', $id));
        return $e;
    }
}
