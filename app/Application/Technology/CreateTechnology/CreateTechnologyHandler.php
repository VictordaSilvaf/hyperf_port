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

namespace App\Application\Technology\CreateTechnology;

use App\Domain\Shared\ValueObject\Slug;
use App\Domain\Technology\Entity\Technology;
use App\Domain\Technology\Repository\TechnologyRepositoryInterface;
use InvalidArgumentException;

final class CreateTechnologyHandler
{
    public function __construct(private readonly TechnologyRepositoryInterface $technologies)
    {
    }

    /** @return array{data: array{id: string, name: string, slug: string}} */
    public function handle(CreateTechnologyCommand $command): array
    {
        $name = trim($command->name);
        if ($name === '') {
            throw new InvalidArgumentException('Technology name cannot be empty.');
        }

        $slug = Slug::fromString($command->slug !== null && trim($command->slug) !== '' ? $command->slug : $name);
        $existing = $this->technologies->findBySlug($slug);
        if ($existing !== null) {
            return ['data' => $this->toArray($existing)];
        }

        $technology = Technology::create($name, $slug);
        $this->technologies->save($technology);

        return ['data' => $this->toArray($technology)];
    }

    /** @return array{id: string, name: string, slug: string} */
    private function toArray(Technology $technology): array
    {
        return [
            'id' => $technology->id()->value(),
            'name' => $technology->name(),
            'slug' => $technology->slug()->value(),
        ];
    }
}
