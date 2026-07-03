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
use App\Domain\Upload\Entity\Upload;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\Upload\ValueObject\UploadId;

final class SetProjectThumbnailHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly UploadRepositoryInterface $uploads,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function handle(string $projectId, string $uploadId): void
    {
        $this->setPath($projectId, $uploadId, 'thumbnail');
    }

    public function setCover(string $projectId, string $uploadId): void
    {
        $this->setPath($projectId, $uploadId, 'cover');
    }

    private function setPath(string $projectId, string $uploadId, string $field): void
    {
        $id = ProjectId::fromString($projectId);
        $project = $this->projects->findById($id);
        if ($project === null) {
            throw ProjectNotFoundException::byId($projectId);
        }
        $upload = $this->uploads->findById(UploadId::fromString($uploadId));
        if ($upload === null) {
            throw new ProjectNotFoundException('Upload not found: ' . $uploadId);
        }

        $updated = $project->replace([$field => $this->resolveStoragePath($upload, $field)]);
        $this->projects->save($updated);
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();
    }

    private function resolveStoragePath(Upload $upload, string $field): string
    {
        if ($field === 'thumbnail') {
            return $upload->thumbnailPath() ?? $upload->webpPath() ?? $upload->path();
        }

        return $upload->webpPath() ?? $upload->path();
    }
}
