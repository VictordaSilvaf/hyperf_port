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

namespace App\Application\User\GetUser;

use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;

final class GetUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function handle(GetUserQuery $query): GetUserResult
    {
        $id = UserId::fromString($query->userId);
        $user = $this->users->findById($id);
        if ($user === null) {
            throw UserNotFoundException::byId($query->userId);
        }

        return new GetUserResult(
            $user->id()->value(),
            $user->name(),
            $user->email()->value(),
        );
    }
}
