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

use App\Application\Upload\UploadJobDispatcherInterface;
use App\Job\ProcessUploadJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;

final class AsyncQueueUploadJobDispatcher implements UploadJobDispatcherInterface
{
    public function __construct(
        private readonly DriverFactory $driverFactory,
    ) {
    }

    public function dispatchProcessUpload(string $uploadId): void
    {
        $this->driverFactory->get('default')->push(new ProcessUploadJob($uploadId));
    }
}
