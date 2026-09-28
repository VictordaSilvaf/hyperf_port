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

namespace App\Infrastructure\Contact;

use App\Application\Contact\ContactRateLimiterInterface;
use App\Domain\Contact\Exception\ContactRateLimitedException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;

final class RedisContactRateLimiter implements ContactRateLimiterInterface
{
    private const KEY_PREFIX = 'contact:rl:';

    public function __construct(
        private readonly Redis $redis,
        private readonly ConfigInterface $config,
    ) {
    }

    public function hit(?string $ipAddress): void
    {
        $key = self::KEY_PREFIX . $this->normalizeKey($ipAddress);
        $max = max(1, (int) $this->config->get('contact.rate_limit.max', 5));
        $window = max(1, (int) $this->config->get('contact.rate_limit.window_seconds', 300));

        $count = (int) $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, $window);
        }

        if ($count > $max) {
            throw ContactRateLimitedException::exceeded();
        }
    }

    private function normalizeKey(?string $ipAddress): string
    {
        $ip = is_string($ipAddress) ? trim($ipAddress) : '';

        return $ip !== '' ? $ip : 'unknown';
    }
}
