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

namespace App\Domain\Upload\Repository;

use App\Domain\Upload\Entity\Upload;
use App\Domain\Upload\ValueObject\UploadId;

interface UploadRepositoryInterface
{
    public function save(Upload $upload): void;

    public function findById(UploadId $id): ?Upload;

    public function delete(UploadId $id): void;
}
