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

namespace App\Application\Project\FlushProjectViews;

use App\Application\Project\ProjectViewCounterInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class FlushProjectViewsHandler
{
    public function __construct(
        private readonly ProjectViewCounterInterface $counter,
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(int $batchSize = 100): int
    {
        $pending = $this->counter->flushPending($batchSize);
        foreach ($pending as $projectId => $count) {
            $this->projects->incrementViews(ProjectId::fromString($projectId), $count);
        }

        return count($pending);
    }
}
