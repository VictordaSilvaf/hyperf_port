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

namespace App\Application\Project\UpdateProject;

final class UpdateProjectCommand
{
    /** @param list<string> $categories @param list<string> $technologies @param list<string> $tags */
    public function __construct(
        public readonly string $projectId,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly ?string $content,
        public readonly ?string $repositoryUrl,
        public readonly ?string $demoUrl,
        public readonly ?string $thumbnail,
        public readonly ?string $cover,
        public readonly ?string $status,
        public readonly bool $featured,
        public readonly array $categories,
        public readonly array $technologies,
        public readonly array $tags,
    ) {
    }
}
