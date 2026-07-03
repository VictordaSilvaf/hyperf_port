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

namespace HyperfTest\Cases\Storage;

use App\Application\Storage\ObjectStorageInterface;
use Hyperf\Testing\TestCase;

use function Swoole\Coroutine\run as co_run;

/**
 * @internal
 * @coversNothing
 */
final class MinioStorageTest extends TestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('swoole')) {
            self::markTestSkipped('The ext-swoole extension is required for MinIO integration tests.');
        }

        if (getenv('FILESYSTEM_DRIVER') !== 'minio') {
            self::markTestSkipped('MinIO tests require FILESYSTEM_DRIVER=minio.');
        }

        parent::setUp();
    }

    public function testWriteReadAndDeleteAgainstMinio(): void
    {
        co_run(function () {
            $storage = $this->container->get(ObjectStorageInterface::class);
            $path = 'tests/' . bin2hex(random_bytes(8)) . '.txt';
            $payload = 'hyper-minio-' . uniqid('', true);

            $storage->write($path, $payload);

            $this->assertTrue($storage->exists($path));
            $this->assertSame($payload, $storage->read($path));

            $storage->delete($path);

            $this->assertFalse($storage->exists($path));
        });
    }
}
