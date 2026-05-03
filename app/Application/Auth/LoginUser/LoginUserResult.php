<?php

declare(strict_types=1);

namespace App\Application\Auth\LoginUser;

final class LoginUserResult
{
    /**
     * @param list<string> $roleSlugs
     * @param list<string> $permissionSlugs
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly array $roleSlugs,
        public readonly array $permissionSlugs,
    ) {
    }
}
