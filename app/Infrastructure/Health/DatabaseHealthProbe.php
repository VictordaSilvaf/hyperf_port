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
use Hyperf\DbConnection\Db;
use Throwable;

final class DatabaseHealthProbe implements HealthProbeInterface
{
    public function name(): string
    {
        return 'database';
    }

    public function isRequiredForReadiness(): bool
    {
        return true;
    }

    public function check(): ComponentHealth
    {
        $started = microtime(true);

        try {
            Db::select('SELECT 1');

            return new ComponentHealth(
                $this->name(),
                'pass',
                latencyMs: (microtime(true) - $started) * 1000,
            );
        } catch (Throwable $e) {
            return new ComponentHealth(
                $this->name(),
                'fail',
                message: 'Database connection failed.',
                latencyMs: (microtime(true) - $started) * 1000,
            );
        }
    }
}
