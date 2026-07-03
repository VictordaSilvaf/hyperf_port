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

namespace App\Application\Upload\StoreUpload;

use App\Application\Storage\ObjectStorageInterface;
use App\Application\Upload\UploadJobDispatcherInterface;
use App\Domain\Upload\Entity\Upload;
use App\Domain\Upload\Repository\UploadRepositoryInterface;

final class StoreUploadHandler
{
    public function __construct(
        private readonly UploadRepositoryInterface $uploads,
        private readonly ObjectStorageInterface $storage,
        private readonly UploadJobDispatcherInterface $uploadJobs,
    ) {
    }

    public function handle(StoreUploadCommand $command): array
    {
        $extension = pathinfo($command->originalName, PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . $extension : '');
        $path = 'uploads/' . date('Y/m/') . $filename;

        $this->storage->write($path, $command->contents);
        $url = $this->storage->publicUrl($path);

        $upload = Upload::create(
            $path,
            $url,
            $command->mimeType,
            strlen($command->contents),
            $command->originalName,
        );
        $this->uploads->save($upload);

        if ($upload->isImage()) {
            $this->uploadJobs->dispatchProcessUpload($upload->id()->value());
        }

        $current = $this->uploads->findById($upload->id()) ?? $upload;

        return [
            'id' => $current->id()->value(),
            'url' => $current->url(),
            'path' => $current->path(),
            'processing_status' => $current->processingStatus()->value,
            'display_url' => $current->displayUrl(),
            'thumbnail_url' => $current->displayThumbnailUrl(),
            'width' => $current->width(),
            'height' => $current->height(),
        ];
    }
}
