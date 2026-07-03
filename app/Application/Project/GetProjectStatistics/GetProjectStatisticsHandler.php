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

namespace App\Application\Project\GetProjectStatistics;

use App\Domain\Project\Repository\ProjectRepositoryInterface;

final class GetProjectStatisticsHandler
{
    public function __construct(private readonly ProjectRepositoryInterface $projects)
    {
    }

    public function handle(): array
    {
        return $this->projects->statistics();
    }
}
