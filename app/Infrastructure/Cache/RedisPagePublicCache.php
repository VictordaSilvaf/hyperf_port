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

use App\Application\Page\PagePublicCacheInterface;
use Hyperf\Redis\Redis;

final class RedisPagePublicCache implements PagePublicCacheInterface
{
    private const VERSION_KEY = 'page:cache:version';

    public function __construct(private readonly Redis $redis)
    {
    }

    public function version(): int
    {
        return (int) ($this->redis->get(self::VERSION_KEY) ?: 1);
    }

    public function bump(): void
    {
        $this->redis->incr(self::VERSION_KEY);
    }

    public function get(string $key): mixed
    {
        $raw = $this->redis->get($this->prefixed($key));
        if ($raw === false || $raw === null) {
            return null;
        }

        return json_decode((string) $raw, true);
    }

    public function set(string $key, mixed $value, int $ttlSeconds = 300): void
    {
        $this->redis->setex(
            $this->prefixed($key),
            $ttlSeconds,
            json_encode($value, JSON_THROW_ON_ERROR)
        );
    }

    private function prefixed(string $key): string
    {
        return 'page:public:v' . $this->version() . ':' . $key;
    }
}
