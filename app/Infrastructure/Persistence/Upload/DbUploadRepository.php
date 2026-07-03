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

namespace App\Infrastructure\Persistence\Upload;

use App\Domain\Upload\Entity\Upload;
use App\Domain\Upload\Repository\UploadRepositoryInterface;
use App\Domain\Upload\ValueObject\UploadId;
use App\Domain\Upload\ValueObject\UploadProcessingStatus;
use Hyperf\DbConnection\Db;

final class DbUploadRepository implements UploadRepositoryInterface
{
    public function save(Upload $upload): void
    {
        $exists = Db::table('uploads')->where('id', $upload->id()->value())->exists();
        $row = [
            'id' => $upload->id()->value(),
            'path' => $upload->path(),
            'url' => $upload->url(),
            'mime_type' => $upload->mimeType(),
            'size' => $upload->size(),
            'original_name' => $upload->originalName(),
            'processing_status' => $upload->processingStatus()->value,
            'webp_path' => $upload->webpPath(),
            'webp_url' => $upload->webpUrl(),
            'thumbnail_path' => $upload->thumbnailPath(),
            'thumbnail_url' => $upload->thumbnailUrl(),
            'width' => $upload->width(),
            'height' => $upload->height(),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($exists) {
            Db::table('uploads')->where('id', $upload->id()->value())->update($row);
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            Db::table('uploads')->insert($row);
        }
    }

    public function findById(UploadId $id): ?Upload
    {
        $row = Db::table('uploads')->where('id', $id->value())->first();
        if ($row === null) {
            return null;
        }

        return $this->toDomain((array) $row);
    }

    public function delete(UploadId $id): void
    {
        Db::table('uploads')->where('id', $id->value())->delete();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function toDomain(array $data): Upload
    {
        return Upload::restore(
            UploadId::fromString((string) $data['id']),
            (string) $data['path'],
            isset($data['url']) ? (string) $data['url'] : null,
            isset($data['mime_type']) ? (string) $data['mime_type'] : null,
            (int) ($data['size'] ?? 0),
            isset($data['original_name']) ? (string) $data['original_name'] : null,
            UploadProcessingStatus::from((string) ($data['processing_status'] ?? UploadProcessingStatus::Skipped->value)),
            isset($data['webp_path']) ? (string) $data['webp_path'] : null,
            isset($data['webp_url']) ? (string) $data['webp_url'] : null,
            isset($data['thumbnail_path']) ? (string) $data['thumbnail_path'] : null,
            isset($data['thumbnail_url']) ? (string) $data['thumbnail_url'] : null,
            isset($data['width']) ? (int) $data['width'] : null,
            isset($data['height']) ? (int) $data['height'] : null,
        );
    }
}
