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

namespace App\Application\Tag\CreateTag;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Tag\Entity\Tag;
use App\Domain\Tag\Repository\TagRepositoryInterface;
use InvalidArgumentException;

final class CreateTagHandler
{
    public function __construct(private readonly TagRepositoryInterface $tags)
    {
    }

    /** @return array{data: array{id: string, name: string, slug: string}} */
    public function handle(CreateTagCommand $command): array
    {
        $name = trim($command->name);
        if ($name === '') {
            throw new InvalidArgumentException('Tag name cannot be empty.');
        }

        $slug = Slug::fromString($command->slug !== null && trim($command->slug) !== '' ? $command->slug : $name);
        $existing = $this->tags->findBySlug($slug);
        if ($existing !== null) {
            return ['data' => $this->toArray($existing)];
        }

        $tag = Tag::create($name, $slug);
        $this->tags->save($tag);

        return ['data' => $this->toArray($tag)];
    }

    /** @return array{id: string, name: string, slug: string} */
    private function toArray(Tag $tag): array
    {
        return [
            'id' => $tag->id()->value(),
            'name' => $tag->name(),
            'slug' => $tag->slug()->value(),
        ];
    }
}
