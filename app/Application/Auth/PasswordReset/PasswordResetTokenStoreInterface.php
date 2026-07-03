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

namespace App\Application\Auth\PasswordReset;

interface PasswordResetTokenStoreInterface
{
    /**
     * Gera um código de uso único (6 dígitos) e associa ao utilizador até expirar.
     */
    public function issue(string $userId): string;

    /**
     * Valida o código, devolve o id do utilizador e remove o código (uso único).
     */
    public function consume(string $token): ?string;
}
