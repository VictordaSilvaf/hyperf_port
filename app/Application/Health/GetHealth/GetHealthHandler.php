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

namespace App\Application\Health\GetHealth;

use App\Application\Health\ComponentHealth;
use App\Application\Health\HealthCheckResult;
use App\Application\Health\HealthProbeInterface;
use DateTimeImmutable;
use DateTimeZone;
use Hyperf\Contract\ConfigInterface;

final class GetHealthHandler
{
    /**
     * @param list<HealthProbeInterface> $probes
     */
    public function __construct(
        private readonly array $probes,
        private readonly ConfigInterface $config,
    ) {
    }

    public function handle(GetHealthQuery $query): HealthCheckResult
    {
        $checks = [];
        $allPass = true;

        foreach ($this->probes as $probe) {
            if ($query->mode === GetHealthQuery::MODE_LIVE && $probe->name() !== 'app') {
                continue;
            }

            if ($query->mode === GetHealthQuery::MODE_READY && ! $probe->isRequiredForReadiness()) {
                continue;
            }

            $result = $probe->check();
            $checks[] = $result;

            if (! $result->isPassing()) {
                $allPass = false;
            }
        }

        if ($checks === []) {
            $checks[] = new ComponentHealth('app', 'pass');
            $allPass = true;
        }

        return new HealthCheckResult(
            status: $allPass ? 'pass' : 'fail',
            service: (string) $this->config->get('app_name', 'hyperf-api'),
            environment: (string) $this->config->get('app_env', 'production'),
            checks: $checks,
            timestamp: (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DATE_ATOM),
        );
    }
}
