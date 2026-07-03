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

namespace App\Application\Project\PatchProject;

final class PatchProjectCommand
{
    /** @param array<string, mixed> $changes */
    public function __construct(
        public readonly string $projectId,
        public readonly array $changes,
    ) {
    }
}
