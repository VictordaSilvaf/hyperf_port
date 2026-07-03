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

namespace HyperfTest\Cases;

use Hyperf\Testing\TestCase;

use function Swoole\Coroutine\run as co_run;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('swoole')) {
            self::markTestSkipped('The ext-swoole extension is required for HTTP integration tests.');
        }
        parent::setUp();
    }

    public function testExample(): void
    {
        // PHPUnit 10+ uses a private runTest(), so Hyperf's RunTestsInCoroutine no longer wraps
        // the test body; HTTP test client requires running inside a Swoole coroutine.
        co_run(function () {
            $this->get('/api/v1/')
                ->assertOk()
                ->assertJsonFragment([
                    'method' => 'GET',
                    'message' => 'Hello Hyperf.',
                ]);
        });
    }
}
