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

final class SpacerBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'spacer';
    }

    public function label(): string
    {
        return 'Spacer';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'size' => ['type' => 'string', 'enum' => ['sm', 'md', 'lg']],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->optionalEnum($payload, 'size', ['sm', 'md', 'lg'], 'md');
    }
}
