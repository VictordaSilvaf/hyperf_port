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

namespace App\Application\Project\ManageProjectImages;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class ReorderProjectImagesHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
    ) {
    }

    /** @param list<array{id: string, order: int}> $items */
    public function handle(string $projectId, array $items): void
    {
        $pid = ProjectId::fromString($projectId);
        if ($this->projects->findById($pid) === null) {
            throw ProjectNotFoundException::byId($projectId);
        }
        $mapped = array_map(static fn (array $item): array => [
            'id' => (string) $item['id'],
            'sort_order' => (int) $item['order'],
        ], $items);
        $this->projects->reorderImages($pid, $mapped);
        $this->cache->bump();
    }
}
