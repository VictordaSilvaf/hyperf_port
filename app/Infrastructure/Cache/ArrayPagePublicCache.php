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

final class ArrayPagePublicCache implements PagePublicCacheInterface
{
    private int $version = 1;

    /** @var array<string, mixed> */
    private array $store = [];

    public function version(): int
    {
        return $this->version;
    }

    public function bump(): void
    {
        ++$this->version;
        $this->store = [];
    }

    public function get(string $key): mixed
    {
        return $this->store[$this->prefixed($key)] ?? null;
    }

    public function set(string $key, mixed $value, int $ttlSeconds = 300): void
    {
        $this->store[$this->prefixed($key)] = $value;
    }

    private function prefixed(string $key): string
    {
        return 'v' . $this->version . ':' . $key;
    }
}
