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

namespace App\Application\Project\Shared;

use App\Application\Storage\ObjectStorageInterface;

/**
 * Enriches project list/detail payloads with absolute CDN URLs when configured.
 */
final class ProjectMediaUrls
{
    public function __construct(private readonly ObjectStorageInterface $storage)
    {
    }

    public function absolute(?string $path): ?string
    {
        return $this->storage->publicUrl($path);
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function enrich(array $item): array
    {
        $thumbnail = isset($item['thumbnail']) && is_string($item['thumbnail']) ? $item['thumbnail'] : null;
        $cover = isset($item['cover']) && is_string($item['cover']) ? $item['cover'] : null;

        $item['thumbnail_url'] = $this->absolute($thumbnail);
        $item['cover_url'] = $this->absolute($cover);

        return $item;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    public function enrichMany(array $items): array
    {
        return array_map(fn (array $item): array => $this->enrich($item), $items);
    }
}
