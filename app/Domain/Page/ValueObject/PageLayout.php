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

namespace App\Domain\Page\ValueObject;

enum PageLayout: string
{
    case Default = 'default';
    case FullWidth = 'full-width';
    case Landing = 'landing';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
