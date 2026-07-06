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

namespace App\Domain\Contact\Repository;

use App\Domain\Contact\Entity\ContactMessage;
use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;

interface ContactMessageRepositoryInterface
{
    public function save(ContactMessage $message): void;

    public function findById(ContactMessageId $id): ?ContactMessage;

    /**
     * @return array{
     *   total: int,
     *   items: list<array<string, mixed>>
     * }
     */
    public function paginate(int $page, int $perPage, ?ContactMessageStatus $status = null): array;
}
