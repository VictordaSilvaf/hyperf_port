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

final class InMemoryUploadRepository implements UploadRepositoryInterface
{
    /** @var array<string, Upload> */
    private array $items = [];

    public function save(Upload $upload): void
    {
        $this->items[$upload->id()->value()] = $upload;
    }

    public function findById(UploadId $id): ?Upload
    {
        return $this->items[$id->value()] ?? null;
    }

    public function delete(UploadId $id): void
    {
        unset($this->items[$id->value()]);
    }
}
