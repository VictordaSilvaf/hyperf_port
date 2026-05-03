<?php

declare(strict_types=1);

namespace App\Application\Auth\PasswordReset;

interface PasswordResetNotifierInterface
{
    /**
     * Deliver reset instructions (email, queue, etc.). Implementations must not throw for unknown users.
     */
    public function notify(string $email, string $plainToken): void;
}
