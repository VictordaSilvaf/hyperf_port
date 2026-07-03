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
        $data = (array) $row;

        return Upload::restore(
            UploadId::fromString((string) $data['id']),
            (string) $data['path'],
            isset($data['url']) ? (string) $data['url'] : null,
            isset($data['mime_type']) ? (string) $data['mime_type'] : null,
            (int) ($data['size'] ?? 0),
            isset($data['original_name']) ? (string) $data['original_name'] : null,
        );
    }

    public function delete(UploadId $id): void
    {
        Db::table('uploads')->where('id', $id->value())->delete();
    }
}
