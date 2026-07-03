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

namespace App\Application\Project\CreateProject;

final class CreateProjectCommand
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly ?string $imagePath,
        public readonly ?string $ownerId,
    ) {
    }
}
