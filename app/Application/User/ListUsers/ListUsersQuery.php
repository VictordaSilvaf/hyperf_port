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

namespace App\Application\User\ListUsers;

final class ListUsersQuery
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?string $search = null,
    ) {
    }
}
