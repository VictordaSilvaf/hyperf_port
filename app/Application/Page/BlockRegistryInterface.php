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

namespace App\Application\Page;

use App\Domain\Page\Block\BlockValidatorInterface;

interface BlockRegistryInterface
{
    public function get(string $type): BlockValidatorInterface;

    /** @return list<BlockValidatorInterface> */
    public function all(): array;

    /** @return list<array{type: string, label: string, schema: array<string, mixed>}> */
    public function metadata(): array;

    /** @param array<string, mixed> $payload */
    public function validate(string $type, array $payload): void;
}
