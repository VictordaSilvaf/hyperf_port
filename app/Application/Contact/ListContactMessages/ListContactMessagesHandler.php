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

namespace App\Application\Contact\ListContactMessages;

use App\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use App\Domain\Contact\ValueObject\ContactMessageStatus;

final class ListContactMessagesHandler
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $messages,
    ) {
    }

    /**
     * @return array{
     *   data: list<array<string, mixed>>,
     *   meta: array{total: int, page: int, per_page: int}
     * }
     */
    public function handle(ListContactMessagesQuery $query): array
    {
        $page = max(1, $query->page);
        $perPage = max(1, min(100, $query->perPage));
        $status = $query->status !== null && $query->status !== ''
            ? ContactMessageStatus::from($query->status)
            : null;

        $raw = $this->messages->paginate($page, $perPage, $status);

        return [
            'data' => $raw['items'],
            'meta' => [
                'total' => $raw['total'],
                'page' => $page,
                'per_page' => $perPage,
            ],
        ];
    }
}
