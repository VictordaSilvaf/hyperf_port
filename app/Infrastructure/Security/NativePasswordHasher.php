<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Shared\Security\PasswordHasherInterface;

final class NativePasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public function verify(string $plain, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        return password_verify($plain, $hash);
    }
}
