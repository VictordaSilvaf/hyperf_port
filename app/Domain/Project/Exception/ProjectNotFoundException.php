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

namespace App\Domain\Project\Exception;

use App\Domain\Shared\DomainException;

final class ProjectNotFoundException extends DomainException
{
    public static function byId(string $id): self
    {
        return new self(sprintf('Project not found: %s', $id));
    }

    public static function bySlug(string $slug): self
    {
        return new self(sprintf('Project not found: %s', $slug));
    }
}
