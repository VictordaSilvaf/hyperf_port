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

namespace App\Application\Storage;

interface ObjectStorageInterface
{
    public function write(string $path, string $contents): void;

    public function read(string $path): string;

    public function delete(string $path): void;

    public function exists(string $path): bool;

    /**
     * Absolute public CDN/base URL for a storage path, or null if unset / empty path.
     */
    public function publicUrl(?string $path): ?string;
}
