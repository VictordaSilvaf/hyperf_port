<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
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
