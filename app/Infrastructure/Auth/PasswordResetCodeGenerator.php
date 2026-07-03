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

namespace App\Infrastructure\Auth;

final class PasswordResetCodeGenerator
{
    /**
     * Código numérico de 6 dígitos (000000–999999), com zeros à esquerda.
     */
    public static function generate(): string
    {
        return sprintf('%06d', random_int(0, 999_999));
    }
}
