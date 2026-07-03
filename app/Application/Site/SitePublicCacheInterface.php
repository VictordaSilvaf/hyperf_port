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

namespace App\Application\Site;

interface SitePublicCacheInterface
{
    public function version(): int;

    public function bump(): void;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttlSeconds = 300): void;
}
