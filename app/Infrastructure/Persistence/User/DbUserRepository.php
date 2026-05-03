<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use Hyperf\DbConnection\Db;

final class DbUserRepository implements UserRepositoryInterface
{
    private const TABLE = 'users';

    public function save(User $user): void
    {
        $row = UserPersistenceMapper::toRow($user);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            Db::table(self::TABLE)->where('id', $row['id'])->update([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $row['password'],
                'updated_at' => $row['updated_at'],
            ]);
        } else {
            Db::table(self::TABLE)->insert([
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $row['password'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ]);
        }
    }

    public function findById(UserId $id): ?User
    {
        $row = Db::table(self::TABLE)->where('id', $id->value())->first();

        return $row === null ? null : $this->rowToUser($row);
    }

    public function findByEmail(Email $email): ?User
    {
        $row = Db::table(self::TABLE)->where('email', $email->value())->first();

        return $row === null ? null : $this->rowToUser($row);
    }

    /**
     * @param array|object $row
     */
    private function rowToUser(mixed $row): User
    {
        $data = is_array($row) ? $row : (array) $row;

        return UserPersistenceMapper::toDomain([
            'id' => (string) $data['id'],
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'password' => isset($data['password']) ? (string) $data['password'] : '',
        ]);
    }
}
