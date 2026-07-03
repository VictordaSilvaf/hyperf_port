<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */

namespace App\Infrastructure\Cache;

use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Infrastructure\Auth\PasswordResetCodeGenerator;
use Hyperf\Redis\Redis;
use RuntimeException;

use function Hyperf\Support\env;

final class RedisPasswordResetTokenStore implements PasswordResetTokenStoreInterface
{
    private const KEY_PREFIX = 'auth:pwd-reset:';

    private const MAX_ATTEMPTS = 64;

    public function __construct(private readonly Redis $redis)
    {
    }

    public function issue(string $userId): string
    {
        $ttl = (int) env('APP_AUTH_RESET_TOKEN_TTL', 3600);

        for ($i = 0; $i < self::MAX_ATTEMPTS; ++$i) {
            $code = PasswordResetCodeGenerator::generate();
            $key = self::KEY_PREFIX . $code;
            $ok = $this->redis->set($key, $userId, ['nx', 'ex' => $ttl]);
            if ($ok) {
                return $code;
            }
        }

        throw new RuntimeException('Could not allocate a unique password reset code.');
    }

    public function consume(string $token): ?string
    {
        $code = self::normalizeCode($token);
        if ($code === null) {
            return null;
        }

        $key = self::KEY_PREFIX . $code;
        $userId = $this->redis->get($key);
        if (! is_string($userId) || $userId === '') {
            return null;
        }

        $this->redis->del($key);

        return $userId;
    }

    private static function normalizeCode(string $raw): ?string
    {
        $trimmed = trim($raw);
        if (! preg_match('/^\d{6}$/', $trimmed)) {
            return null;
        }

        return $trimmed;
    }
}
