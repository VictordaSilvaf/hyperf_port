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

namespace App\Domain\Post\ValueObject;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
