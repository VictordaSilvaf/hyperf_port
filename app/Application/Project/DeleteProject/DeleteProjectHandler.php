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

namespace App\Application\Project\DeleteProject;

use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;

final class DeleteProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {
    }

    public function handle(DeleteProjectCommand $command): void
    {
        $id = ProjectId::fromString($command->projectId);
        if ($this->projects->findById($id) === null) {
            throw ProjectNotFoundException::byId($command->projectId);
        }

        $this->projects->delete($id);
    }
}
