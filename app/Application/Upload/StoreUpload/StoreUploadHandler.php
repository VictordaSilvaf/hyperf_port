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
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final class StoreUploadHandler
{
    public function __construct(
        private readonly UploadRepositoryInterface $uploads,
        private readonly ObjectStorageInterface $storage,
        private readonly UploadJobDispatcherInterface $uploadJobs,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(StoreUploadCommand $command): array
    {
        if ($command->contents === '') {
            throw new RuntimeException('Uploaded file is empty.');
        }

        $extension = pathinfo($command->originalName, PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . ($extension !== '' ? '.' . strtolower($extension) : '');
        $path = 'uploads/' . date('Y/m/') . $filename;

        try {
            $this->storage->write($path, $command->contents);
        } catch (Throwable $exception) {
            $this->logger->error('Object storage write failed: ' . $exception->getMessage(), [
                'path' => $path,
                'bytes' => strlen($command->contents),
            ]);
            throw new RuntimeException(
                'Object storage write failed. Check FILESYSTEM_DRIVER / R2_* credentials and endpoint.',
                0,
                $exception,
            );
        }

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
            try {
                $this->uploadJobs->dispatchProcessUpload($upload->id()->value());
            } catch (Throwable $exception) {
                // File is already stored — do not fail the HTTP upload because of queue/processing.
                $this->logger->warning('Upload processing dispatch failed: ' . $exception->getMessage(), [
                    'upload_id' => $upload->id()->value(),
                ]);
            }
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
