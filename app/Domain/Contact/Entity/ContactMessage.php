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

namespace App\Domain\Contact\Entity;

use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;
use App\Domain\User\ValueObject\Email;
use DateTimeImmutable;
use InvalidArgumentException;

final class ContactMessage
{
    private function __construct(
        private readonly ContactMessageId $id,
        private readonly string $name,
        private readonly Email $email,
        private readonly ?string $subject,
        private readonly string $body,
        private readonly ContactMessageStatus $status,
        private readonly ?string $ipAddress,
        private readonly ?string $userAgent,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        string $name,
        string $email,
        ?string $subject,
        string $body,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): self {
        $trimmedName = trim($name);
        if ($trimmedName === '') {
            throw new InvalidArgumentException('Contact name cannot be empty.');
        }

        $trimmedBody = trim($body);
        if ($trimmedBody === '') {
            throw new InvalidArgumentException('Contact message body cannot be empty.');
        }

        $trimmedSubject = $subject !== null ? trim($subject) : null;
        if ($trimmedSubject === '') {
            $trimmedSubject = null;
        }

        return new self(
            ContactMessageId::generate(),
            $trimmedName,
            Email::fromString($email),
            $trimmedSubject,
            $trimmedBody,
            ContactMessageStatus::New,
            self::nullableString($ipAddress),
            self::nullableString($userAgent),
            new DateTimeImmutable(),
        );
    }

    public static function restore(
        ContactMessageId $id,
        string $name,
        string $email,
        ?string $subject,
        string $body,
        ContactMessageStatus $status,
        ?string $ipAddress,
        ?string $userAgent,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $name,
            Email::fromString($email),
            $subject,
            $body,
            $status,
            $ipAddress,
            $userAgent,
            $createdAt,
        );
    }

    public function markRead(): self
    {
        if ($this->status === ContactMessageStatus::Read) {
            return $this;
        }

        return new self(
            $this->id,
            $this->name,
            $this->email,
            $this->subject,
            $this->body,
            ContactMessageStatus::Read,
            $this->ipAddress,
            $this->userAgent,
            $this->createdAt,
        );
    }

    public function archive(): self
    {
        if ($this->status === ContactMessageStatus::Archived) {
            return $this;
        }

        return new self(
            $this->id,
            $this->name,
            $this->email,
            $this->subject,
            $this->body,
            ContactMessageStatus::Archived,
            $this->ipAddress,
            $this->userAgent,
            $this->createdAt,
        );
    }

    public function withStatus(ContactMessageStatus $status): self
    {
        return match ($status) {
            ContactMessageStatus::New => $this,
            ContactMessageStatus::Read => $this->markRead(),
            ContactMessageStatus::Archived => $this->archive(),
        };
    }

    public function id(): ContactMessageId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function status(): ContactMessageStatus
    {
        return $this->status;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => $this->name,
            'email' => $this->email->value(),
            'subject' => $this->subject,
            'body' => $this->body,
            'status' => $this->status->value,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'created_at' => $this->createdAt->format(DATE_ATOM),
        ];
    }

    private static function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
