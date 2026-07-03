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
use App\Domain\Technology\ValueObject\TechnologyId;

final class TechStackBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'tech_stack';
    }

    public function label(): string
    {
        return 'Tech Stack';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'maxLength' => 200],
                'technology_ids' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'uuid']],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->optionalString($payload, 'title');
        if (! isset($payload['technology_ids']) || ! is_array($payload['technology_ids'])) {
            return;
        }
        foreach ($payload['technology_ids'] as $item) {
            if (! is_string($item) || trim($item) === '') {
                throw InvalidBlockPayloadException::forType($this->type(), 'technology_ids must contain valid UUIDs.');
            }
            TechnologyId::fromString(trim($item));
        }
    }
}
