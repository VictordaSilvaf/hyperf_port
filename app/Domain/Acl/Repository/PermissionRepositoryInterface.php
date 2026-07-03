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

namespace App\Domain\Acl\Repository;

interface PermissionRepositoryInterface
{
    /**
     * @return list<array{id: string, slug: string, description: ?string}>
     */
    public function all(): array;

    public function findIdBySlug(string $slug): ?string;
}
