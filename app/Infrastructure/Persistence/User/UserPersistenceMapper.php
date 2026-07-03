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

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;

final class UserPersistenceMapper
{
    /**
     * @param array{id: string, name: string, email: string, password?: null|string} $row
     */
    public static function toDomain(array $row): User
    {
        return User::restore(
            UserId::fromString($row['id']),
            $row['name'],
            Email::fromString($row['email']),
            (string) ($row['password'] ?? ''),
        );
    }

    /**
     * @return array{id: string, name: string, email: string, password: string, created_at: string, updated_at: string}
     */
    public static function toRow(User $user): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id' => $user->id()->value(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'password' => $user->passwordHash(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
