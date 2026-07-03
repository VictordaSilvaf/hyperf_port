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

namespace App\Domain\User\Entity;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use InvalidArgumentException;

final class User
{
    private function __construct(
        private readonly UserId $id,
        private readonly string $name,
        private readonly Email $email,
        private readonly string $passwordHash,
    ) {
    }

    public static function register(string $name, Email $email, string $passwordHash): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Name cannot be empty.');
        }
        if ($passwordHash === '') {
            throw new InvalidArgumentException('Password hash cannot be empty.');
        }

        return new self(UserId::generate(), $trimmed, $email, $passwordHash);
    }

    public static function restore(UserId $id, string $name, Email $email, string $passwordHash): self
    {
        return new self($id, $name, $email, $passwordHash);
    }

    public function changePassword(string $newPasswordHash): self
    {
        if ($newPasswordHash === '') {
            throw new InvalidArgumentException('Password hash cannot be empty.');
        }

        return new self($this->id, $this->name, $this->email, $newPasswordHash);
    }

    public function withProfile(string $name, Email $email): self
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Name cannot be empty.');
        }

        return new self($this->id, $trimmed, $email, $this->passwordHash);
    }

    public function id(): UserId
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

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }
}
