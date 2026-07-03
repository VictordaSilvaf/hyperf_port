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

final class CtaBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'cta';
    }

    public function label(): string
    {
        return 'Call to Action';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['label', 'href'],
            'properties' => [
                'label' => ['type' => 'string', 'maxLength' => 100],
                'href' => ['type' => 'string'],
                'variant' => ['type' => 'string', 'enum' => ['primary', 'secondary', 'outline']],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireNonEmptyString($payload, 'label', $this->type());
        $this->requireNonEmptyString($payload, 'href', $this->type());
        $this->optionalEnum($payload, 'variant', ['primary', 'secondary', 'outline'], 'primary');
    }
}
