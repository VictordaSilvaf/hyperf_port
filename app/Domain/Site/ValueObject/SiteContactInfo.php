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

namespace App\Domain\Site\ValueObject;

final class SiteContactInfo
{
    /**
     * @param null|array<string, mixed> $address
     */
    private function __construct(
        private readonly ?string $email,
        private readonly ?string $phone,
        private readonly ?string $whatsapp,
        private readonly ?array $address,
        private readonly ?string $notificationEmail,
    ) {
    }

    /**
     * @param null|array<string, mixed> $data
     */
    public static function fromArray(?array $data): self
    {
        $data = $data ?? [];

        return new self(
            self::nullableString($data['email'] ?? null),
            self::nullableString($data['phone'] ?? null),
            self::nullableString($data['whatsapp'] ?? null),
            isset($data['address']) && is_array($data['address']) ? $data['address'] : null,
            self::nullableString($data['notification_email'] ?? null),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address,
            'notification_email' => $this->notificationEmail,
        ];
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function whatsapp(): ?string
    {
        return $this->whatsapp;
    }

    /** @return null|array<string, mixed> */
    public function address(): ?array
    {
        return $this->address;
    }

    public function notificationEmail(): ?string
    {
        return $this->notificationEmail;
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
