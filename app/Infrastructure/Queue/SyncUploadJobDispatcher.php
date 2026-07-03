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

namespace App\Infrastructure\Queue;

use App\Application\Upload\ProcessUploadImage\ProcessUploadImageHandler;
use App\Application\Upload\UploadJobDispatcherInterface;

final class SyncUploadJobDispatcher implements UploadJobDispatcherInterface
{
    public function __construct(
        private readonly ProcessUploadImageHandler $processor,
    ) {
    }

    public function dispatchProcessUpload(string $uploadId): void
    {
        $this->processor->handle($uploadId);
    }
}
