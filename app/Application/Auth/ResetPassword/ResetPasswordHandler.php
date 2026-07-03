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

namespace App\Application\Auth\ResetPassword;

use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Application\Shared\Security\PasswordHasherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\UserId;

final class ResetPasswordHandler
{
    public function __construct(
        private readonly PasswordResetTokenStoreInterface $tokens,
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function handle(ResetPasswordCommand $command): void
    {
        $userId = $this->tokens->consume($command->code);
        if ($userId === null) {
            throw new InvalidCredentialsException();
        }

        $id = UserId::fromString($userId);
        $user = $this->users->findById($id);
        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        $updated = $user->changePassword($this->passwordHasher->hash($command->password));
        $this->users->save($updated);
    }
}
