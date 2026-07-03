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

namespace App\Presentation\Http\Controllers\Public;

use App\Application\Project\GetProjectBySlug\GetProjectBySlugHandler;
use App\Application\Project\GetProjectBySlug\GetProjectBySlugQuery;
use App\Application\Project\ListProjects\ListProjectsHandler;
use App\Application\Project\ListProjects\ListProjectsQuery;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\ValueObject\ProjectStatus;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\Resources\Project\ProjectResource;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class ProjectController extends AbstractController
{
    #[Inject]
    protected ListProjectsHandler $listProjects;

    #[Inject]
    protected GetProjectBySlugHandler $getProjectBySlug;

    public function index(): array
    {
        return $this->listProjects->handle(new ListProjectsQuery(
            (int) $this->request->input('page', 1),
            (int) $this->request->input('per_page', 15),
            null,
            ProjectStatus::Published,
        ));
    }

    public function show(string $slug): array|PsrResponseInterface
    {
        try {
            $result = $this->getProjectBySlug->handle(new GetProjectBySlugQuery($slug, true));
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }

        return ProjectResource::fromResult($result);
    }
}
