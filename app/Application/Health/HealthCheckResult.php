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

namespace App\Application\Health;

final readonly class HealthCheckResult
{
    /**
     * @param list<ComponentHealth> $checks
     */
    public function __construct(
        public string $status,
        public string $service,
        public string $environment,
        public array $checks,
        public string $timestamp,
    ) {
    }

    public function httpStatusCode(): int
    {
        return $this->status === 'pass' ? 200 : 503;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $checks = [];
        foreach ($this->checks as $check) {
            $checks[$check->name] = $check->toArray();
        }

        return [
            'status' => $this->status,
            'service' => $this->service,
            'environment' => $this->environment,
            'timestamp' => $this->timestamp,
            'checks' => $checks,
        ];
    }
}
