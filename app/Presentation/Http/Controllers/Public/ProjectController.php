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

use App\Application\Category\ListCategories\ListCategoriesHandler;
use App\Application\Project\GetProjectBySlug\GetProjectBySlugHandler;
use App\Application\Project\GetRelatedProjects\GetRelatedProjectsHandler;
use App\Application\Project\ListProjects\ListProjectsHandler;
use App\Application\Project\SearchProjects\SearchProjectsHandler;
use App\Application\Tag\ListTags\ListTagsHandler;
use App\Application\Technology\ListTechnologies\ListTechnologiesHandler;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Job\FlushProjectViewsJob;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

final class ProjectController extends AbstractController
{
    #[Inject]
    protected ListProjectsHandler $listProjects;

    #[Inject]
    protected GetProjectBySlugHandler $getProjectBySlug;

    #[Inject]
    protected GetRelatedProjectsHandler $related;

    #[Inject]
    protected SearchProjectsHandler $search;

    #[Inject]
    protected DriverFactory $queue;

    public function index(): array
    {
        return $this->listProjects->fromQueryParams($this->request->all(), true);
    }

    public function show(string $slug): array|PsrResponseInterface
    {
        try {
            $result = $this->getProjectBySlug->handle($slug, true);
            $this->queue->get('default')->push(new FlushProjectViewsJob());

            return $result;
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function related(string $slug): array|PsrResponseInterface
    {
        try {
            return $this->related->handle($slug);
        } catch (ProjectNotFoundException) {
            return $this->response->json(['message' => trans('http.project_not_found')])->withStatus(404);
        }
    }

    public function search(): array
    {
        return $this->search->handle(
            (string) $this->request->input('q', ''),
            (int) $this->request->input('page', 1),
            (int) $this->request->input('per_page', 15),
        );
    }
}

final class TaxonomyController extends AbstractController
{
    #[Inject]
    protected ListCategoriesHandler $categories;

    #[Inject]
    protected ListTechnologiesHandler $technologies;

    #[Inject]
    protected ListTagsHandler $tags;

    public function categories(): array
    {
        return ['data' => $this->categories->handle()];
    }

    public function technologies(): array
    {
        return ['data' => $this->technologies->handle()];
    }

    public function tags(): array
    {
        return ['data' => $this->tags->handle()];
    }
}
