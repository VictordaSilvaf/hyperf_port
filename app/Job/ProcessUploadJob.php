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

namespace App\Job;

use App\Application\Upload\ProcessUploadImage\ProcessUploadImageHandler;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;

final class ProcessUploadJob extends Job
{
    public function __construct(
        public readonly string $uploadId,
    ) {
    }

    public function handle(): void
    {
        $handler = ApplicationContext::getContainer()->get(ProcessUploadImageHandler::class);
        $handler->handle($this->uploadId);
    }
}
