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

use App\Domain\User\Repository\UserRepositoryInterface;

final class ListUsersHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * @return array{
     *   data: list<array{id: string, name: string, email: string, created_at: ?string, updated_at: ?string}>,
     *   meta: array{total: int, page: int, per_page: int, last_page: int}
     * }
     */
    public function handle(ListUsersQuery $query): array
    {
        $page = max(1, $query->page);
        $perPage = max(1, min(100, $query->perPage));
        $raw = $this->users->paginatedSummaries($page, $perPage, $query->search);
        $total = $raw['total'];
        $lastPage = $perPage > 0 ? (int) max(1, (int) ceil($total / $perPage)) : 1;

        return [
            'data' => $raw['items'],
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => $lastPage,
            ],
        ];
    }
}
