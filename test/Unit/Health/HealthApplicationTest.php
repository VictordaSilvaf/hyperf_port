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

namespace HyperfTest\Unit\Health;

use App\Application\Health\ComponentHealth;
use App\Application\Health\GetHealth\GetHealthHandler;
use App\Application\Health\GetHealth\GetHealthQuery;
use App\Application\Health\HealthProbeInterface;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class HealthApplicationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLiveOnlyChecksApplicationProbe(): void
    {
        $appProbe = Mockery::mock(HealthProbeInterface::class);
        $appProbe->shouldReceive('name')->andReturn('app');
        $appProbe->shouldReceive('check')->once()->andReturn(new ComponentHealth('app', 'pass'));

        $dbProbe = Mockery::mock(HealthProbeInterface::class);
        $dbProbe->shouldReceive('name')->andReturn('database');
        $dbProbe->shouldNotReceive('check');

        $handler = new GetHealthHandler([$appProbe, $dbProbe], $this->config());

        $result = $handler->handle(new GetHealthQuery(GetHealthQuery::MODE_LIVE));

        $this->assertSame('pass', $result->status);
        $this->assertSame(200, $result->httpStatusCode());
        $this->assertCount(1, $result->checks);
        $this->assertSame('app', $result->checks[0]->name);
    }

    public function testReadyFailsWhenRequiredProbeFails(): void
    {
        $appProbe = Mockery::mock(HealthProbeInterface::class);
        $appProbe->shouldReceive('name')->andReturn('app');
        $appProbe->shouldReceive('isRequiredForReadiness')->andReturn(true);
        $appProbe->shouldReceive('check')->once()->andReturn(new ComponentHealth('app', 'pass'));

        $dbProbe = Mockery::mock(HealthProbeInterface::class);
        $dbProbe->shouldReceive('name')->andReturn('database');
        $dbProbe->shouldReceive('isRequiredForReadiness')->andReturn(true);
        $dbProbe->shouldReceive('check')->once()->andReturn(new ComponentHealth('database', 'fail', 'down'));

        $handler = new GetHealthHandler([$appProbe, $dbProbe], $this->config());

        $result = $handler->handle(new GetHealthQuery(GetHealthQuery::MODE_READY));

        $this->assertSame('fail', $result->status);
        $this->assertSame(503, $result->httpStatusCode());
        $this->assertSame('fail', $result->toArray()['checks']['database']['status']);
    }

    private function config(): ConfigInterface
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('app_name', 'hyperf-api')->andReturn('VictorDev');
        $config->shouldReceive('get')->with('app_env', 'production')->andReturn('testing');

        return $config;
    }
}
