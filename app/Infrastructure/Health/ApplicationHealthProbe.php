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

final class ApplicationHealthProbe implements HealthProbeInterface
{
    public function name(): string
    {
        return 'app';
    }

    public function isRequiredForReadiness(): bool
    {
        return true;
    }

    public function check(): ComponentHealth
    {
        return new ComponentHealth($this->name(), 'pass');
    }
}
