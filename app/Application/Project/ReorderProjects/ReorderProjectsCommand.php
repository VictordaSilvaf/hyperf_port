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

namespace App\Application\Project\ReorderProjects;

final class ReorderProjectsCommand
{
    /**
     * @param list<array{id: string, sort_order: int}> $items
     */
    public function __construct(
        public readonly array $items,
    ) {
    }
}
