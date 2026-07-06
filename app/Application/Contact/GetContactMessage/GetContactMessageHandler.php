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

namespace App\Application\Contact\GetContactMessage;

use App\Domain\Contact\Exception\ContactMessageNotFoundException;
use App\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;

final class GetContactMessageHandler
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $messages,
    ) {
    }

    /** @return array{data: array<string, mixed>} */
    public function handle(GetContactMessageQuery $query): array
    {
        $id = ContactMessageId::fromString($query->id);
        $message = $this->messages->findById($id);
        if ($message === null) {
            throw ContactMessageNotFoundException::byId($query->id);
        }

        if ($query->markAsRead && $message->status() === ContactMessageStatus::New) {
            $message = $message->markRead();
            $this->messages->save($message);
        }

        return ['data' => $message->toArray()];
    }
}
