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
use App\Domain\Project\Entity\ProjectImage;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\Upload\ValueObject\UploadId;

final class AddProjectImageHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly UploadRepositoryInterface $uploads,
        private readonly ProjectPublicCacheInterface $cache,
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

        return ['id' => $image->id()->value()];
    }
}

final class RemoveProjectImageHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
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
    }
}

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

final class SetProjectThumbnailHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly UploadRepositoryInterface $uploads,
        private readonly ProjectPublicCacheInterface $cache,
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

        $updated = $project->replace([$field => $upload->path()]);
        $this->projects->save($updated);
        $this->cache->bump();
    }
}
