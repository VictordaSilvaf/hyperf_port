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

use App\Domain\Page\Exception\InvalidBlockPayloadException;

final class HeroBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Hero';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['headline'],
            'properties' => [
                'headline' => ['type' => 'string', 'maxLength' => 200],
                'subheadline' => ['type' => 'string', 'maxLength' => 500],
                'image_id' => ['type' => 'string', 'format' => 'uuid'],
                'cta' => [
                    'type' => 'object',
                    'properties' => [
                        'label' => ['type' => 'string'],
                        'href' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireNonEmptyString($payload, 'headline', $this->type());
        $this->optionalString($payload, 'subheadline');
        $this->optionalUuid($payload, 'image_id');
        if (isset($payload['cta'])) {
            if (! is_array($payload['cta'])) {
                throw InvalidBlockPayloadException::forType($this->type(), 'cta must be an object.');
            }
            $this->requireNonEmptyString($payload['cta'], 'label', $this->type());
            $this->requireNonEmptyString($payload['cta'], 'href', $this->type());
        }
    }
}
