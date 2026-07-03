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

namespace App\Application\Page\PatchPage;

final class PatchPageCommand
{
    /** @param array<string, mixed> $changes */
    public function __construct(
        public readonly string $pageId,
        public readonly array $changes,
    ) {
    }
}
