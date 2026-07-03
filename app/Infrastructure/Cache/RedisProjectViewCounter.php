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
use Hyperf\Redis\Redis;

final class RedisProjectViewCounter implements ProjectViewCounterInterface
{
    private const PENDING_PREFIX = 'project:views:pending:';

    private const DIRTY_SET = 'project:views:dirty';

    public function __construct(private readonly Redis $redis)
    {
    }

    public function increment(string $projectId): void
    {
        $this->redis->incr(self::PENDING_PREFIX . $projectId);
        $this->redis->sAdd(self::DIRTY_SET, $projectId);
    }

    public function flushPending(int $batchSize = 100): array
    {
        $flushed = [];
        for ($i = 0; $i < $batchSize; ++$i) {
            $projectId = $this->redis->sPop(self::DIRTY_SET);
            if ($projectId === false || $projectId === null) {
                break;
            }
            $key = self::PENDING_PREFIX . $projectId;
            $count = (int) $this->redis->getDel($key);
            if ($count > 0) {
                $flushed[(string) $projectId] = $count;
            }
        }

        return $flushed;
    }
}
