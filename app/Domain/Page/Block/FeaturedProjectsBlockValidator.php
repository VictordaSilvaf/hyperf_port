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

final class FeaturedProjectsBlockValidator implements BlockValidatorInterface
{
    use BlockValidationHelpers;

    public function type(): string
    {
        return 'featured_projects';
    }

    public function label(): string
    {
        return 'Featured Projects';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'maxLength' => 200],
                'project_ids' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'uuid']],
                'layout' => ['type' => 'string', 'enum' => ['grid-2', 'grid-3', 'carousel']],
            ],
        ];
    }

    public function validate(array $payload): void
    {
        $this->optionalString($payload, 'title');
        $this->optionalProjectIdList($payload, 'project_ids');
        $this->optionalEnum($payload, 'layout', ['grid-2', 'grid-3', 'carousel'], 'grid-3');
    }
}
