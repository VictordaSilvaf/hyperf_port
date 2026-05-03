<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use Hyperf\Context\Context;

final class AuthContext
{
    public const USER_ID_KEY = 'auth.user_id';

    public static function setUserId(string $userId): void
    {
        Context::set(self::USER_ID_KEY, $userId);
    }

    public static function userId(): ?string
    {
        $id = Context::get(self::USER_ID_KEY);
        return is_string($id) ? $id : null;
    }
}
