<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;

final class NoOpPasswordResetNotifier implements PasswordResetNotifierInterface
{
    public function notify(string $email, string $plainToken): void
    {
    }
}
