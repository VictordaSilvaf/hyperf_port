<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    /**
     * @return array{
     *   total: int,
     *   items: list<array{id: string, name: string, email: string, created_at: ?string, updated_at: ?string}>
     * }
     */
    public function paginatedSummaries(int $page, int $perPage, ?string $search = null): array;
}
