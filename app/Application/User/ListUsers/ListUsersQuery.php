<?php

declare(strict_types=1);

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
