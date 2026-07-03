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

interface HealthProbeInterface
{
    public function name(): string;

    /** Included in readiness checks (live ignores non-app probes). */
    public function isRequiredForReadiness(): bool;

    public function check(): ComponentHealth;
}
