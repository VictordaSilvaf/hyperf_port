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

final class ImageBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return 'Image';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['upload_id'],
            'properties' => [
                'upload_id' => ['type' => 'string', 'format' => 'uuid'],
                'caption' => ['type' => 'string', 'maxLength' => 255],
                'alt' => ['type' => 'string', 'maxLength' => 255],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->requireUuid($payload, 'upload_id', $this->type());
        $this->optionalString($payload, 'caption');
        $this->optionalString($payload, 'alt');
    }
}
