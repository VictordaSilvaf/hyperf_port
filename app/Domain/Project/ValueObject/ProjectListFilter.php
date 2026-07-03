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

namespace App\Domain\Project\ValueObject;

final class ProjectListFilter
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $search = null,
        public readonly ?ProjectStatus $status = null,
        public readonly ?bool $featured = null,
        public readonly ?string $categorySlug = null,
        public readonly ?string $technologySlug = null,
        public readonly ?string $tagSlug = null,
        public readonly string $sort = 'sort_order',
        public readonly string $direction = 'asc',
        public readonly bool $publicOnly = false,
        public readonly bool $withTrashed = false,
    ) {
    }
}
