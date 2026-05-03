<?php

declare(strict_types=1);

namespace App\Application\User\GetUser;

final class GetUserResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
    ) {
    }
}
