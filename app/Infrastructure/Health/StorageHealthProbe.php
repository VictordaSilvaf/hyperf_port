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
use App\Application\Storage\ObjectStorageInterface;
use Hyperf\Contract\ConfigInterface;
use Throwable;

use function Hyperf\Support\env;

final class StorageHealthProbe implements HealthProbeInterface
{
    private const PROBE_PATH = '.health/storage-probe.txt';

    public function __construct(
        private readonly ObjectStorageInterface $storage,
        private readonly ConfigInterface $config,
    ) {
    }

    public function name(): string
    {
        return 'storage';
    }

    public function isRequiredForReadiness(): bool
    {
        return env('APP_STORAGE_HEALTH_REQUIRED', 'false') === 'true';
    }

    public function check(): ComponentHealth
    {
        $started = microtime(true);
        $driver = (string) $this->config->get('file.default', 'minio');

        if ($driver === 'memory') {
            return new ComponentHealth(
                $this->name(),
                'pass',
                message: 'In-memory driver (no remote storage).',
                latencyMs: (microtime(true) - $started) * 1000,
            );
        }

        try {
            $token = 'ok-' . bin2hex(random_bytes(8));
            $this->storage->write(self::PROBE_PATH, $token);

            if ($this->storage->read(self::PROBE_PATH) !== $token) {
                return new ComponentHealth(
                    $this->name(),
                    'fail',
                    message: 'Storage read/write mismatch.',
                    latencyMs: (microtime(true) - $started) * 1000,
                );
            }

            $this->storage->delete(self::PROBE_PATH);

            return new ComponentHealth(
                $this->name(),
                'pass',
                latencyMs: (microtime(true) - $started) * 1000,
            );
        } catch (Throwable $throwable) {
            return new ComponentHealth(
                $this->name(),
                'fail',
                message: 'Storage check failed: ' . $throwable->getMessage(),
                latencyMs: (microtime(true) - $started) * 1000,
            );
        }
    }
}
