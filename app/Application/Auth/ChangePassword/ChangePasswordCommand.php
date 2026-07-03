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

namespace App\Application\Auth\ChangePassword;

final class ChangePasswordCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly string $currentPassword,
        public readonly string $newPassword,
    ) {
    }
}
