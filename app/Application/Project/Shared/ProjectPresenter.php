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

namespace App\Application\Project\Shared;

use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Tag\Repository\TagRepositoryInterface;
use App\Domain\Technology\Repository\TechnologyRepositoryInterface;
use App\Domain\Upload\Repository\UploadRepositoryInterface;

final class ProjectPresenter
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly CategoryRepositoryInterface $categories,
        private readonly TechnologyRepositoryInterface $technologies,
        private readonly TagRepositoryInterface $tags,
        private readonly UploadRepositoryInterface $uploads,
    ) {
    }

    public function toDetail(Project $project): array
    {
        $id = $project->id();
        $images = [];
        foreach ($this->projects->imagesFor($id) as $image) {
            $upload = $this->uploads->findById($image->uploadId());
            $images[] = [
                'id' => $image->id()->value(),
                'upload_id' => $image->uploadId()->value(),
                'caption' => $image->caption(),
                'order' => $image->sortOrder(),
                'url' => $upload?->url(),
                'path' => $upload?->path(),
            ];
        }

        return [
            'id' => $id->value(),
            'title' => $project->title(),
            'slug' => $project->slug()->value(),
            'description' => $project->description(),
            'content' => $project->content(),
            'repository_url' => $project->repositoryUrl(),
            'demo_url' => $project->demoUrl(),
            'thumbnail' => $project->thumbnailPath(),
            'cover' => $project->coverPath(),
            'status' => $project->status()->value,
            'featured' => $project->featured(),
            'published_at' => $project->publishedAt()?->format(DATE_ATOM),
            'order' => $project->sortOrder(),
            'views' => $project->views(),
            'categories' => $this->mapTaxonomy($this->categories->findByIds($this->projects->categoryIdsFor($id))),
            'technologies' => $this->mapTaxonomy($this->technologies->findByIds($this->projects->technologyIdsFor($id))),
            'tags' => $this->mapTaxonomy($this->tags->findByIds($this->projects->tagIdsFor($id))),
            'images' => $images,
        ];
    }

    public function toSummaryFromId(ProjectId $id): ?array
    {
        $project = $this->projects->findById($id);
        if ($project === null) {
            return null;
        }

        return $this->toDetail($project);
    }

    /**
     * @param list<object> $items
     * @return list<array{id: string, name: string, slug: string}>
     */
    private function mapTaxonomy(array $items): array
    {
        return array_map(static fn ($item): array => [
            'id' => $item->id()->value(),
            'name' => $item->name(),
            'slug' => $item->slug()->value(),
        ], $items);
    }
}
