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

namespace App\Infrastructure\Mail;

use App\Application\Contact\ContactMessageNotifierInterface;
use App\Domain\Contact\Entity\ContactMessage;

final class NoOpContactMessageNotifier implements ContactMessageNotifierInterface
{
    public function notify(string $to, ContactMessage $message): void
    {
    }
}
