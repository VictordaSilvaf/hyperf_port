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

namespace App\Domain\Contact\Exception;

use App\Domain\Shared\DomainException;

final class ContactCaptchaFailedException extends DomainException
{
    public static function invalid(): self
    {
        return new self('Contact captcha verification failed.');
    }
}
