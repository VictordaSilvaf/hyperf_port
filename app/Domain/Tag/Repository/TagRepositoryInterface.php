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

namespace App\Domain\Tag\Repository;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\ValueObject\TagId;

interface TagRepositoryInterface
{
    public function findById(TagId $id): ?Tag;

    public function findBySlug(Slug $slug): ?Tag;

    /** @return list<Tag> */
    public function all(): array;

    /** @param list<string> $ids */
    public function findByIds(array $ids): array;
}
