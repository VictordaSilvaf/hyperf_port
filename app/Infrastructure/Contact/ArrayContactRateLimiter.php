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

/**
 * In-process rate limiter (dev / when Redis store is not selected).
 * Not shared across workers — prefer Redis in production.
 */
final class ArrayContactRateLimiter implements ContactRateLimiterInterface
{
    /** @var array<string, list<int>> */
    private array $hits = [];

    public function __construct(private readonly ConfigInterface $config)
    {
    }

    public function hit(?string $ipAddress): void
    {
        $key = $this->normalizeKey($ipAddress);
        $max = max(1, (int) $this->config->get('contact.rate_limit.max', 5));
        $window = max(1, (int) $this->config->get('contact.rate_limit.window_seconds', 300));
        $now = time();
        $cutoff = $now - $window;

        $bucket = array_values(array_filter(
            $this->hits[$key] ?? [],
            static fn (int $ts): bool => $ts > $cutoff,
        ));

        if (count($bucket) >= $max) {
            $this->hits[$key] = $bucket;
            throw ContactRateLimitedException::exceeded();
        }

        $bucket[] = $now;
        $this->hits[$key] = $bucket;
    }

    private function normalizeKey(?string $ipAddress): string
    {
        $ip = is_string($ipAddress) ? trim($ipAddress) : '';

        return $ip !== '' ? $ip : 'unknown';
    }
}
