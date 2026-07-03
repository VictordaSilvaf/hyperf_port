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

namespace App\Domain\Technology\Repository;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Technology\Entity\Technology;
use App\Domain\Technology\ValueObject\TechnologyId;

interface TechnologyRepositoryInterface
{
    public function findById(TechnologyId $id): ?Technology;

    public function findBySlug(Slug $slug): ?Technology;

    /** @return list<Technology> */
    public function all(): array;

    /** @param list<string> $ids */
    public function findByIds(array $ids): array;
}
