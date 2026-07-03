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

namespace App\Application\Tag\ListTags;

use App\Domain\Tag\Repository\TagRepositoryInterface;

final class ListTagsHandler
{
    public function __construct(private readonly TagRepositoryInterface $tags)
    {
    }

    public function handle(): array
    {
        return array_map(static fn ($t): array => [
            'id' => $t->id()->value(),
            'name' => $t->name(),
            'slug' => $t->slug()->value(),
        ], $this->tags->all());
    }
}
