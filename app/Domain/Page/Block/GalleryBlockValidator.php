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

final class GalleryBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'gallery';
    }

    public function label(): string
    {
        return 'Gallery';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['upload_ids'],
            'properties' => [
                'upload_ids' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'uuid']],
                'columns' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 6],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireUuidList($payload, 'upload_ids', $this->type());
        if (isset($payload['columns'])) {
            if (! is_int($payload['columns']) || $payload['columns'] < 1 || $payload['columns'] > 6) {
                throw InvalidBlockPayloadException::forType($this->type(), 'columns must be between 1 and 6.');
            }
        }
    }
}
