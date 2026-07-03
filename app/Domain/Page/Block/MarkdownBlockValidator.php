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

final class MarkdownBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'markdown';
    }

    public function label(): string
    {
        return 'Markdown';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['content'],
            'properties' => [
                'content' => ['type' => 'string'],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireNonEmptyString($payload, 'content', $this->type());
    }
}
