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

namespace HyperfTest\Unit\Storage;

use App\Infrastructure\Storage\FlysystemObjectStorage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Filesystem\FilesystemFactory;
use League\Flysystem\Filesystem;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class FlysystemObjectStorageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testPublicUrlBuildsFromConfiguredBase(): void
    {
        $filesystem = Mockery::mock(Filesystem::class);

        $factory = Mockery::mock(FilesystemFactory::class);
        $factory->shouldReceive('get')->with('memory')->andReturn($filesystem);

        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('file.default', 'minio')->andReturn('memory');
        $config->shouldReceive('get')->with('file.prefix', '')->andReturn('development');
        $config->shouldReceive('get')->with('file.public_url', '')->andReturn('https://cdn.example.com/victorsf');

        $storage = new FlysystemObjectStorage($factory, $config);

        $this->assertSame(
            'https://cdn.example.com/victorsf/development/uploads/photo.jpg',
            $storage->publicUrl('/uploads/photo.jpg'),
        );
    }

    public function testWriteAppliesEnvironmentPrefix(): void
    {
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('write')
            ->once()
            ->with('development/uploads/photo.jpg', 'payload');

        $factory = Mockery::mock(FilesystemFactory::class);
        $factory->shouldReceive('get')->with('memory')->andReturn($filesystem);

        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('file.default', 'minio')->andReturn('memory');
        $config->shouldReceive('get')->with('file.prefix', '')->andReturn('development');

        $storage = new FlysystemObjectStorage($factory, $config);
        $storage->write('uploads/photo.jpg', 'payload');

        $this->addToAssertionCount(1);
    }

    public function testPublicUrlReturnsNullWhenBaseMissing(): void
    {
        $filesystem = Mockery::mock(Filesystem::class);

        $factory = Mockery::mock(FilesystemFactory::class);
        $factory->shouldReceive('get')->with('memory')->andReturn($filesystem);

        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('file.default', 'minio')->andReturn('memory');
        $config->shouldReceive('get')->with('file.prefix', '')->andReturn('');
        $config->shouldReceive('get')->with('file.public_url', '')->andReturn('');

        $storage = new FlysystemObjectStorage($factory, $config);

        $this->assertNull($storage->publicUrl('file.txt'));
    }
}
