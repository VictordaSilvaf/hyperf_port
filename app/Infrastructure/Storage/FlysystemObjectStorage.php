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

namespace App\Infrastructure\Storage;

use App\Application\Storage\ObjectStorageInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Filesystem\FilesystemFactory;
use League\Flysystem\Filesystem;

final class FlysystemObjectStorage implements ObjectStorageInterface
{
    public function __construct(
        private readonly FilesystemFactory $factory,
        private readonly ConfigInterface $config,
    ) {
    }

    public function write(string $path, string $contents): void
    {
        $this->disk()->write($this->normalizePath($path), $contents);
    }

    public function read(string $path): string
    {
        return $this->disk()->read($this->normalizePath($path));
    }

    public function delete(string $path): void
    {
        $this->disk()->delete($this->normalizePath($path));
    }

    public function exists(string $path): bool
    {
        return $this->disk()->fileExists($this->normalizePath($path));
    }

    public function publicUrl(string $path): ?string
    {
        $base = (string) $this->config->get('file.public_url', '');
        if ($base === '') {
            return null;
        }

        return rtrim($base, '/') . '/' . ltrim($this->normalizePath($path), '/');
    }

    private function disk(): Filesystem
    {
        $driver = (string) $this->config->get('file.default', 'minio');

        return $this->factory->get($driver);
    }

    private function normalizePath(string $path): string
    {
        $path = ltrim($path, '/');
        $prefix = trim((string) $this->config->get('file.prefix', ''), '/');

        if ($prefix === '') {
            return $path;
        }

        return $prefix . '/' . $path;
    }
}
