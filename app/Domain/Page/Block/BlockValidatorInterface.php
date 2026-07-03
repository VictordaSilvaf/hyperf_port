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

namespace App\Domain\Page\Block;

interface BlockValidatorInterface
{
    public function type(): string;

    public function label(): string;

    /** @return array<string, mixed> */
    public function schema(): array;

    /** @param array<string, mixed> $payload */
    public function validate(array $payload): void;
}
