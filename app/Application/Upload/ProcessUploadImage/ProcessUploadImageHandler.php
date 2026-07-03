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

namespace App\Application\Upload\ProcessUploadImage;

use App\Application\Storage\ObjectStorageInterface;
use App\Application\Upload\ImageProcessorInterface;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\Upload\ValueObject\UploadId;
use App\Domain\Upload\ValueObject\UploadProcessingStatus;
use Psr\Log\LoggerInterface;
use Throwable;

final class ProcessUploadImageHandler
{
    public function __construct(
        private readonly UploadRepositoryInterface $uploads,
        private readonly ObjectStorageInterface $storage,
        private readonly ImageProcessorInterface $processor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(string $uploadId): void
    {
        $id = UploadId::fromString($uploadId);
        $upload = $this->uploads->findById($id);
        if ($upload === null) {
            return;
        }

        if ($upload->processingStatus() === UploadProcessingStatus::Completed
            || $upload->processingStatus() === UploadProcessingStatus::Skipped) {
            return;
        }

        if (! $upload->isImage() || ! $this->processor->supports((string) $upload->mimeType())) {
            $this->uploads->save($upload->markSkipped());

            return;
        }

        $this->uploads->save($upload->markProcessing());

        try {
            $contents = $this->storage->read($upload->path());
            $result = $this->processor->process($contents, (string) $upload->mimeType());

            $basePath = preg_replace('/\.[^.]+$/', '', $upload->path()) ?? $upload->path();
            $optimizedPath = $this->optimizedPath($upload->path(), $result->optimizedMimeType);
            $webpPath = $basePath . '.webp';
            $thumbnailPath = $basePath . '_thumb.webp';

            $this->storage->write($optimizedPath, $result->optimizedContents);
            $this->storage->write($webpPath, $result->webpContents);
            $this->storage->write($thumbnailPath, $result->thumbnailContents);

            if ($optimizedPath !== $upload->path()) {
                $this->storage->delete($upload->path());
            }

            $processed = $upload->withProcessedVariants(
                $optimizedPath,
                $this->storage->publicUrl($optimizedPath),
                strlen($result->optimizedContents),
                $webpPath,
                $this->storage->publicUrl($webpPath),
                $thumbnailPath,
                $this->storage->publicUrl($thumbnailPath),
                $result->width,
                $result->height,
            );

            $this->uploads->save($processed);
        } catch (Throwable $exception) {
            $this->logger->error('Upload image processing failed: ' . $exception->getMessage(), [
                'upload_id' => $uploadId,
            ]);
            $this->uploads->save($upload->markFailed());
        }
    }

    private function optimizedPath(string $originalPath, string $mimeType): string
    {
        if ($mimeType === 'image/jpeg') {
            $path = preg_replace('/\.[^.]+$/', '.jpg', $originalPath);

            return $path ?? $originalPath;
        }

        if ($mimeType === 'image/png') {
            return $originalPath;
        }

        if ($mimeType === 'image/webp') {
            return $originalPath;
        }

        $path = preg_replace('/\.[^.]+$/', '.jpg', $originalPath);

        return $path ?? $originalPath;
    }
}
