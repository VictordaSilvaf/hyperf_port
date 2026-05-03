<?php

declare(strict_types=1);

namespace App\Application\Auth;

interface AccessTokenIssuerInterface
{
    public function issue(string $userId): string;
}
