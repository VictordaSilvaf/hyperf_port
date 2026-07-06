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

namespace App\Application\Contact\SubmitContactMessage;

final class SubmitContactMessageCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $subject,
        public readonly string $body,
        public readonly ?string $captchaToken,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
    ) {
    }
}
