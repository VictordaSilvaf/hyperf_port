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

namespace App\Infrastructure\Persistence\Contact;

use App\Domain\Contact\Entity\ContactMessage;
use App\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;

final class InMemoryContactMessageRepository implements ContactMessageRepositoryInterface
{
    /** @var array<string, ContactMessage> */
    private array $messages = [];

    public function save(ContactMessage $message): void
    {
        $this->messages[$message->id()->value()] = $message;
    }

    public function findById(ContactMessageId $id): ?ContactMessage
    {
        return $this->messages[$id->value()] ?? null;
    }

    public function paginate(int $page, int $perPage, ?ContactMessageStatus $status = null): array
    {
        $filtered = array_values(array_filter(
            $this->messages,
            static fn (ContactMessage $message): bool => $status === null || $message->status() === $status,
        ));

        usort(
            $filtered,
            static fn (ContactMessage $a, ContactMessage $b): int => $b->createdAt() <=> $a->createdAt(),
        );

        $total = count($filtered);
        $offset = max(0, ($page - 1) * $perPage);
        $slice = array_slice($filtered, $offset, $perPage);

        $items = array_map(
            static fn (ContactMessage $message): array => ContactMessagePersistenceMapper::toSummary($message),
            $slice,
        );

        return ['total' => $total, 'items' => $items];
    }
}
