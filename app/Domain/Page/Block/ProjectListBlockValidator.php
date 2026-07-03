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

use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Page\Exception\InvalidBlockPayloadException;
use App\Domain\Tag\ValueObject\TagId;

final class ProjectListBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'project_list';
    }

    public function label(): string
    {
        return 'Project List';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'maxLength' => 200],
                'category_id' => ['type' => 'string', 'format' => 'uuid'],
                'tag_id' => ['type' => 'string', 'format' => 'uuid'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
                'layout' => ['type' => 'string', 'enum' => ['grid-2', 'grid-3', 'list']],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->optionalString($payload, 'title');
        if (isset($payload['category_id']) && is_string($payload['category_id']) && $payload['category_id'] !== '') {
            CategoryId::fromString($payload['category_id']);
        }
        if (isset($payload['tag_id']) && is_string($payload['tag_id']) && $payload['tag_id'] !== '') {
            TagId::fromString($payload['tag_id']);
        }
        if (isset($payload['limit'])) {
            if (! is_int($payload['limit']) || $payload['limit'] < 1 || $payload['limit'] > 50) {
                throw InvalidBlockPayloadException::forType($this->type(), 'limit must be between 1 and 50.');
            }
        }
        $this->optionalEnum($payload, 'layout', ['grid-2', 'grid-3', 'list'], 'grid-3');
    }
}
