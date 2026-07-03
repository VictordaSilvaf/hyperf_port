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

namespace App\Infrastructure\Auth;

use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use RuntimeException;

/**
 * In-process store for tests and single-worker dev. Do not use in multi-worker production.
 */
final class ArrayPasswordResetTokenStore implements PasswordResetTokenStoreInterface
{
    private const MAX_ATTEMPTS = 64;

    /** @var array<string, array{user_id: string, expires_at: int}> */
    private array $tokens = [];

    public function issue(string $userId): string
    {
        $ttl = (int) \Hyperf\Support\env('APP_AUTH_RESET_TOKEN_TTL', 3600);
        $expiresAt = time() + $ttl;

        for ($i = 0; $i < self::MAX_ATTEMPTS; ++$i) {
            $code = PasswordResetCodeGenerator::generate();
            if (isset($this->tokens[$code])) {
                continue;
            }
            $this->tokens[$code] = [
                'user_id' => $userId,
                'expires_at' => $expiresAt,
            ];

            return $code;
        }

        throw new RuntimeException('Could not allocate a unique password reset code.');
    }

    public function consume(string $token): ?string
    {
        $code = self::normalizeCode($token);
        if ($code === null) {
            return null;
        }

        if (! isset($this->tokens[$code])) {
            return null;
        }

        $entry = $this->tokens[$code];
        unset($this->tokens[$code]);

        if ($entry['expires_at'] < time()) {
            return null;
        }

        return $entry['user_id'];
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
