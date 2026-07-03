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
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private array $items = [];

    public function save(User $user): void
    {
        $this->items[$user->id()->value()] = $user;
    }

    public function findById(UserId $id): ?User
    {
        return $this->items[$id->value()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->items as $user) {
            if ($user->email()->value() === $email->value()) {
                return $user;
            }
        }

        return null;
    }

    public function paginatedSummaries(int $page, int $perPage, ?string $search = null): array
    {
        $list = array_values($this->items);
        $trimmed = $search !== null ? trim($search) : '';
        if ($trimmed !== '') {
            $needle = strtolower($trimmed);
            $list = array_values(array_filter(
                $list,
                static function (User $u) use ($needle): bool {
                    return str_contains(strtolower($u->name()), $needle)
                        || str_contains(strtolower($u->email()->value()), $needle);
                }
            ));
        }

        usort($list, static function (User $a, User $b): int {
            return strcmp($a->email()->value(), $b->email()->value());
        });

        $total = count($list);
        $offset = max(0, ($page - 1) * $perPage);
        $slice = array_slice($list, $offset, $perPage);
        $items = [];
        foreach ($slice as $user) {
            $items[] = [
                'id' => $user->id()->value(),
                'name' => $user->name(),
                'email' => $user->email()->value(),
                'created_at' => null,
                'updated_at' => null,
            ];
        }

        return ['total' => $total, 'items' => $items];
    }
}
