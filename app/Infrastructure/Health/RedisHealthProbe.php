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

namespace App\Infrastructure\Health;

use App\Application\Health\ComponentHealth;
use App\Application\Health\HealthProbeInterface;
use Hyperf\Redis\Redis;
use Throwable;

final class RedisHealthProbe implements HealthProbeInterface
{
    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    public function name(): string
    {
        return 'redis';
    }

    public function isRequiredForReadiness(): bool
    {
        return true;
    }

    public function check(): ComponentHealth
    {
        $started = microtime(true);

        try {
            $pong = $this->redis->ping();

            if ($pong === false || $pong === null) {
                return new ComponentHealth(
                    $this->name(),
                    'fail',
                    message: 'Redis ping returned empty response.',
                    latencyMs: (microtime(true) - $started) * 1000,
                );
            }

            return new ComponentHealth(
                $this->name(),
                'pass',
                latencyMs: (microtime(true) - $started) * 1000,
            );
        } catch (Throwable) {
            return new ComponentHealth(
                $this->name(),
                'fail',
                message: 'Redis connection failed.',
                latencyMs: (microtime(true) - $started) * 1000,
            );
        }
    }
}
