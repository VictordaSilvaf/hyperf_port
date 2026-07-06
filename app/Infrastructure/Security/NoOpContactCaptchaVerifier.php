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

namespace App\Infrastructure\Security;

use App\Application\Contact\ContactCaptchaVerifierInterface;

final class NoOpContactCaptchaVerifier implements ContactCaptchaVerifierInterface
{
    public function verify(string $token, ?string $remoteIp): bool
    {
        return true;
    }
}
