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

use App\Application\Project\FlushProjectViews\FlushProjectViewsHandler;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;

final class FlushProjectViewsJob extends Job
{
    public function handle(): void
    {
        $handler = ApplicationContext::getContainer()->get(FlushProjectViewsHandler::class);
        $handler->handle();
    }
}
