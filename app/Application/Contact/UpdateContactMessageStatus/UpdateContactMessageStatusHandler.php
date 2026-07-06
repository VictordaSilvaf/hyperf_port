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

namespace App\Application\Contact\UpdateContactMessageStatus;

use App\Domain\Contact\Exception\ContactMessageNotFoundException;
use App\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;

final class UpdateContactMessageStatusHandler
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $messages,
    ) {
    }

    /** @return array{data: array<string, mixed>} */
    public function handle(UpdateContactMessageStatusCommand $command): array
    {
        $id = ContactMessageId::fromString($command->id);
        $message = $this->messages->findById($id);
        if ($message === null) {
            throw ContactMessageNotFoundException::byId($command->id);
        }

        $status = ContactMessageStatus::from($command->status);
        $updated = $message->withStatus($status);
        $this->messages->save($updated);

        return ['data' => $updated->toArray()];
    }
}
