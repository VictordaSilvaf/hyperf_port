<?php

declare(strict_types=1);

namespace App\Application\Auth\ResetPassword;

final class ResetPasswordCommand
{
    public function __construct(
        public readonly string $code,
        public readonly string $password,
    ) {
    }
}
