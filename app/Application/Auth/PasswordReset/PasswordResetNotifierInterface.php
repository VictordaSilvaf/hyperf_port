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

namespace App\Application\Auth\PasswordReset;

interface PasswordResetNotifierInterface
{
    /**
     * Deliver reset instructions (email, queue, etc.). Implementations must not throw for unknown users.
     */
    public function notify(string $email, string $plainToken): void;
}
