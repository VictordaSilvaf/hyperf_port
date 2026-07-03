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

namespace App\Application\Auth\RefreshAccessToken;

use App\Application\Auth\AccessTokenIssuerInterface;
use App\Application\Auth\InvalidCredentialsException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;

final class RefreshAccessTokenHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AccessTokenIssuerInterface $accessTokens,
    ) {
    }

    public function handle(string $userId): string
    {
        $id = UserId::fromString($userId);
        if ($this->users->findById($id) === null) {
            throw new InvalidCredentialsException();
        }

        return $this->accessTokens->issue($userId);
    }
}
