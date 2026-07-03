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

final readonly class ComponentHealth
{
    public function __construct(
        public string $name,
        public string $status,
        public ?string $message = null,
        public ?float $latencyMs = null,
    ) {
    }

    public function isPassing(): bool
    {
        return $this->status === 'pass';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['status' => $this->status];

        if ($this->message !== null) {
            $data['message'] = $this->message;
        }

        if ($this->latencyMs !== null) {
            $data['latency_ms'] = round($this->latencyMs, 2);
        }

        return $data;
    }
}
