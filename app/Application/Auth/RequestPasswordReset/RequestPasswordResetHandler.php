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

namespace App\Application\Auth\RequestPasswordReset;

use App\Application\Auth\PasswordReset\PasswordResetNotifierInterface;
use App\Application\Auth\PasswordReset\PasswordResetTokenStoreInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;

final class RequestPasswordResetHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordResetTokenStoreInterface $tokens,
        private readonly PasswordResetNotifierInterface $notifier,
    ) {
    }

    public function handle(RequestPasswordResetCommand $command): void
    {
        $email = Email::fromString($command->email);
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return;
        }

        $plainToken = $this->tokens->issue($user->id()->value());
        $this->notifier->notify($email->value(), $plainToken);
    }
}
