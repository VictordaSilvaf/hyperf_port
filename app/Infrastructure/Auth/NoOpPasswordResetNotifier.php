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

namespace App\Infrastructure\Auth;

use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;

final class NoOpPasswordResetNotifier implements PasswordResetNotifierInterface
{
    public function notify(string $email, string $plainToken): void
    {
    }
}
