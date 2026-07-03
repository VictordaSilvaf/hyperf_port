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
use App\Domain\Project\Entity\ProjectImage;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\Upload\ValueObject\UploadId;

final class AddProjectImageHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly UploadRepositoryInterface $uploads,
        private readonly ProjectPublicCacheInterface $cache,
        private readonly PublicContentCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function handle(string $projectId, string $uploadId, ?string $caption): array
    {
        $id = ProjectId::fromString($projectId);
        if ($this->projects->findById($id) === null) {
            throw ProjectNotFoundException::byId($projectId);
        }
        if ($this->uploads->findById(UploadId::fromString($uploadId)) === null) {
            throw new ProjectNotFoundException('Upload not found: ' . $uploadId);
        }

        $images = $this->projects->imagesFor($id);
        $order = $images === [] ? 1 : max(array_map(fn ($i) => $i->sortOrder(), $images)) + 1;
        $image = ProjectImage::create($id, UploadId::fromString($uploadId), $caption, $order);
        $this->projects->saveImage($image);
        $this->cache->bump();
        $this->cacheInvalidator->invalidatePages();

        return ['id' => $image->id()->value()];
    }
}
