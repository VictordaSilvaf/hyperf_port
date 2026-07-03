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
use App\Application\Shared\PublicContentCacheInvalidatorInterface;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;

final class RemoveProjectImageHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function handle(string $projectId, string $imageId): void
    {
        $pid = ProjectId::fromString($projectId);
        $image = $this->projects->findImageById(ProjectImageId::fromString($imageId));
        if ($image === null || $image->projectId()->value() !== $pid->value()) {
            throw ProjectNotFoundException::byId($imageId);
        }
        $this->projects->deleteImage(ProjectImageId::fromString($imageId));
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();
    }
}
