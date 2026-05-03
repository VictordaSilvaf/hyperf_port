<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use Hyperf\Context\Context;

final class AuthContext
{
    public const USER_ID_KEY = 'auth.user_id';

    /** @var string */
    public const PERMISSION_SLUGS_KEY = 'auth.permission_slugs';

    public static function setUserId(string $userId): void
    {
        Context::set(self::USER_ID_KEY, $userId);
    }

    public static function userId(): ?string
    {
        $id = Context::get(self::USER_ID_KEY);
        return is_string($id) ? $id : null;
    }

    /**
     * @param list<string> $slugs
     */
    public static function setPermissionSlugs(array $slugs): void
    {
        Context::set(self::PERMISSION_SLUGS_KEY, array_values(array_unique($slugs)));
    }

    /**
     * @return list<string>
     */
    public static function permissionSlugs(): array
    {
        $v = Context::get(self::PERMISSION_SLUGS_KEY);
        return is_array($v) ? $v : [];
    }

    public static function can(string $permissionSlug): bool
    {
        return in_array($permissionSlug, self::permissionSlugs(), true);
    }

    public static function clear(): void
    {
        Context::set(self::USER_ID_KEY, null);
        Context::set(self::PERMISSION_SLUGS_KEY, []);
    }
}
