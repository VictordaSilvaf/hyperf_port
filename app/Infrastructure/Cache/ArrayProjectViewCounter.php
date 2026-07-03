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

namespace App\Infrastructure\Cache;

use App\Application\Project\ProjectViewCounterInterface;

final class ArrayProjectViewCounter implements ProjectViewCounterInterface
{
    /** @var array<string, int> */
    private array $pending = [];

    public function increment(string $projectId): void
    {
        $this->pending[$projectId] = ($this->pending[$projectId] ?? 0) + 1;
    }

    public function flushPending(int $batchSize = 100): array
    {
        $flushed = array_slice($this->pending, 0, $batchSize, true);
        foreach (array_keys($flushed) as $id) {
            unset($this->pending[$id]);
        }

        return $flushed;
    }
}
