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

namespace App\Application\Page\ListPages;

final class ListPagesQuery
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly bool $publicOnly = false,
    ) {
    }
}
