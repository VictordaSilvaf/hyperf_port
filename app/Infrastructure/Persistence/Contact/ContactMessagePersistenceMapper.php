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
use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;
use DateTimeImmutable;

final class ContactMessagePersistenceMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public static function toDomain(array $row): ContactMessage
    {
        return ContactMessage::restore(
            ContactMessageId::fromString((string) $row['id']),
            (string) $row['name'],
            (string) $row['email'],
            isset($row['subject']) ? (string) $row['subject'] : null,
            (string) $row['body'],
            ContactMessageStatus::from((string) $row['status']),
            isset($row['ip_address']) ? (string) $row['ip_address'] : null,
            isset($row['user_agent']) ? (string) $row['user_agent'] : null,
            new DateTimeImmutable((string) $row['created_at']),
        );
    }

    /** @return array<string, mixed> */
    public static function toRow(ContactMessage $message): array
    {
        return [
            'id' => $message->id()->value(),
            'name' => $message->name(),
            'email' => $message->email()->value(),
            'subject' => $message->subject(),
            'body' => $message->body(),
            'status' => $message->status()->value,
            'ip_address' => $message->ipAddress(),
            'user_agent' => $message->userAgent(),
            'created_at' => $message->createdAt()->format('Y-m-d H:i:s'),
        ];
    }

    /** @return array<string, mixed> */
    public static function toSummary(ContactMessage $message): array
    {
        return [
            'id' => $message->id()->value(),
            'name' => $message->name(),
            'email' => $message->email()->value(),
            'subject' => $message->subject(),
            'status' => $message->status()->value,
            'created_at' => $message->createdAt()->format(DATE_ATOM),
        ];
    }
}
