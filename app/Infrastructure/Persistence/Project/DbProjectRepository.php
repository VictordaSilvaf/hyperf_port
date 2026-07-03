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

namespace App\Infrastructure\Persistence\Project;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Entity\ProjectImage;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectId;
use App\Domain\Project\ValueObject\ProjectImageId;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectSlug;
use App\Domain\Project\ValueObject\ProjectStatus;
use Hyperf\DbConnection\Db;

final class DbProjectRepository implements ProjectRepositoryInterface
{
    private const TABLE = 'projects';

    public function save(Project $project): void
    {
        $row = ProjectPersistenceMapper::toRow($project);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            unset($row['created_at']);
            Db::table(self::TABLE)->where('id', $row['id'])->update($row);
        } else {
            Db::table(self::TABLE)->insert($row);
        }
    }

    public function softDelete(ProjectId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function restore(ProjectId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->update(['deleted_at' => null]);
    }

    public function forceDelete(ProjectId $id): void
    {
        Db::table(self::TABLE)->where('id', $id->value())->delete();
    }

    public function findById(ProjectId $id, bool $withTrashed = false): ?Project
    {
        $builder = Db::table(self::TABLE)->where('id', $id->value());
        if (! $withTrashed) {
            $builder->whereNull('deleted_at');
        }
        $row = $builder->first();

        return $row === null ? null : ProjectPersistenceMapper::toDomain((array) $row);
    }

    public function findBySlug(ProjectSlug $slug, bool $publicOnly = false): ?Project
    {
        $builder = Db::table(self::TABLE)->where('slug', $slug->value())->whereNull('deleted_at');
        if ($publicOnly) {
            $builder->where('status', ProjectStatus::Published->value);
        }
        $row = $builder->first();

        return $row === null ? null : ProjectPersistenceMapper::toDomain((array) $row);
    }

    public function nextSortOrder(): int
    {
        $max = Db::table(self::TABLE)->max('sort_order');

        return $max === null ? 1 : ((int) $max) + 1;
    }

    public function incrementViews(ProjectId $id, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        Db::table(self::TABLE)->where('id', $id->value())->increment('views', $amount);
    }

    public function paginate(ProjectListFilter $filter): array
    {
        $builder = Db::table(self::TABLE . ' as p')->select('p.*');

        if (! $filter->withTrashed) {
            $builder->whereNull('p.deleted_at');
        }
        if ($filter->publicOnly) {
            $builder->where('p.status', ProjectStatus::Published->value);
        } elseif ($filter->status !== null) {
            $builder->where('p.status', $filter->status->value);
        }
        if ($filter->featured !== null) {
            $builder->where('p.featured', $filter->featured);
        }
        if ($filter->search !== null && trim($filter->search) !== '') {
            $term = '%' . addcslashes(trim($filter->search), '%_\\') . '%';
            $builder->where(static function ($q) use ($term): void {
                $q->where('p.title', 'ilike', $term)
                    ->orWhere('p.slug', 'ilike', $term)
                    ->orWhere('p.description', 'ilike', $term)
                    ->orWhere('p.content', 'ilike', $term);
            });
        }
        if ($filter->categorySlug !== null) {
            $builder->join('category_project as cp', 'cp.project_id', '=', 'p.id')
                ->join('categories as c', 'c.id', '=', 'cp.category_id')
                ->where('c.slug', $filter->categorySlug);
        }
        if ($filter->technologySlug !== null) {
            $builder->join('project_technology as pt', 'pt.project_id', '=', 'p.id')
                ->join('technologies as t', 't.id', '=', 'pt.technology_id')
                ->where('t.slug', $filter->technologySlug);
        }
        if ($filter->tagSlug !== null) {
            $builder->join('project_tag as ptag', 'ptag.project_id', '=', 'p.id')
                ->join('tags as tg', 'tg.id', '=', 'ptag.tag_id')
                ->where('tg.slug', $filter->tagSlug);
        }

        $sort = in_array($filter->sort, ['sort_order', 'title', 'created_at', 'published_at', 'views'], true)
            ? $filter->sort
            : 'sort_order';
        $direction = strtolower($filter->direction) === 'desc' ? 'desc' : 'asc';

        $total = (clone $builder)->distinct()->count('p.id');
        $rows = $builder->distinct()
            ->orderBy('p.' . $sort, $direction)
            ->forPage($filter->page, $filter->perPage)
            ->get();

        return ['total' => (int) $total, 'items' => array_map(fn ($row) => $this->summaryFromRow((array) $row), $rows->all())];
    }

    public function statistics(): array
    {
        $base = Db::table(self::TABLE)->whereNull('deleted_at');

        return [
            'published' => (clone $base)->where('status', ProjectStatus::Published->value)->count(),
            'draft' => (clone $base)->where('status', ProjectStatus::Draft->value)->count(),
            'archived' => (clone $base)->where('status', ProjectStatus::Archived->value)->count(),
            'views' => (int) (clone $base)->sum('views'),
            'featured' => (clone $base)->where('featured', true)->count(),
        ];
    }

    public function syncCategories(ProjectId $projectId, array $categoryIds): void
    {
        Db::table('category_project')->where('project_id', $projectId->value())->delete();
        foreach ($categoryIds as $categoryId) {
            Db::table('category_project')->insert([
                'project_id' => $projectId->value(),
                'category_id' => (string) $categoryId,
            ]);
        }
    }

    public function syncTechnologies(ProjectId $projectId, array $technologyIds): void
    {
        Db::table('project_technology')->where('project_id', $projectId->value())->delete();
        foreach ($technologyIds as $technologyId) {
            Db::table('project_technology')->insert([
                'project_id' => $projectId->value(),
                'technology_id' => (string) $technologyId,
            ]);
        }
    }

    public function syncTags(ProjectId $projectId, array $tagIds): void
    {
        Db::table('project_tag')->where('project_id', $projectId->value())->delete();
        foreach ($tagIds as $tagId) {
            Db::table('project_tag')->insert([
                'project_id' => $projectId->value(),
                'tag_id' => (string) $tagId,
            ]);
        }
    }

    public function categoryIdsFor(ProjectId $projectId): array
    {
        return Db::table('category_project')->where('project_id', $projectId->value())->pluck('category_id')->map(fn ($id) => (string) $id)->all();
    }

    public function technologyIdsFor(ProjectId $projectId): array
    {
        return Db::table('project_technology')->where('project_id', $projectId->value())->pluck('technology_id')->map(fn ($id) => (string) $id)->all();
    }

    public function tagIdsFor(ProjectId $projectId): array
    {
        return Db::table('project_tag')->where('project_id', $projectId->value())->pluck('tag_id')->map(fn ($id) => (string) $id)->all();
    }

    public function saveImage(ProjectImage $image): void
    {
        $exists = Db::table('project_images')->where('id', $image->id()->value())->exists();
        $row = [
            'id' => $image->id()->value(),
            'project_id' => $image->projectId()->value(),
            'upload_id' => $image->uploadId()->value(),
            'caption' => $image->caption(),
            'sort_order' => $image->sortOrder(),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($exists) {
            Db::table('project_images')->where('id', $image->id()->value())->update($row);
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            Db::table('project_images')->insert($row);
        }
    }

    public function deleteImage(ProjectImageId $imageId): void
    {
        Db::table('project_images')->where('id', $imageId->value())->delete();
    }

    public function findImageById(ProjectImageId $imageId): ?ProjectImage
    {
        $row = Db::table('project_images')->where('id', $imageId->value())->first();

        return $row === null ? null : ProjectPersistenceMapper::imageToDomain((array) $row);
    }

    public function imagesFor(ProjectId $projectId): array
    {
        $rows = Db::table('project_images')->where('project_id', $projectId->value())->orderBy('sort_order')->get();

        return array_map(fn ($row) => ProjectPersistenceMapper::imageToDomain((array) $row), $rows->all());
    }

    public function reorderImages(ProjectId $projectId, array $items): void
    {
        foreach ($items as $item) {
            Db::table('project_images')
                ->where('project_id', $projectId->value())
                ->where('id', (string) $item['id'])
                ->update(['sort_order' => (int) $item['sort_order'], 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function relatedPublished(ProjectId $projectId, int $limit = 6): array
    {
        $categoryIds = $this->categoryIdsFor($projectId);
        $technologyIds = $this->technologyIdsFor($projectId);
        $tagIds = $this->tagIdsFor($projectId);

        if ($categoryIds === [] && $technologyIds === [] && $tagIds === []) {
            return [];
        }

        $builder = Db::table(self::TABLE . ' as p')
            ->select('p.*')
            ->where('p.id', '!=', $projectId->value())
            ->where('p.status', ProjectStatus::Published->value)
            ->whereNull('p.deleted_at')
            ->where(static function ($q) use ($categoryIds, $technologyIds, $tagIds): void {
                if ($categoryIds !== []) {
                    $q->orWhereExists(static function ($sub) use ($categoryIds): void {
                        $sub->select(Db::raw('1'))
                            ->from('category_project as cp')
                            ->whereColumn('cp.project_id', 'p.id')
                            ->whereIn('cp.category_id', $categoryIds);
                    });
                }
                if ($technologyIds !== []) {
                    $q->orWhereExists(static function ($sub) use ($technologyIds): void {
                        $sub->select(Db::raw('1'))
                            ->from('project_technology as pt')
                            ->whereColumn('pt.project_id', 'p.id')
                            ->whereIn('pt.technology_id', $technologyIds);
                    });
                }
                if ($tagIds !== []) {
                    $q->orWhereExists(static function ($sub) use ($tagIds): void {
                        $sub->select(Db::raw('1'))
                            ->from('project_tag as ptag')
                            ->whereColumn('ptag.project_id', 'p.id')
                            ->whereIn('ptag.tag_id', $tagIds);
                    });
                }
            })
            ->orderByDesc('p.featured')
            ->orderBy('p.sort_order')
            ->limit($limit);

        return array_map(fn ($row) => $this->summaryFromRow((array) $row), $builder->get()->all());
    }

    public function search(string $query, int $page, int $perPage): array
    {
        return $this->paginate(new ProjectListFilter(
            page: $page,
            perPage: $perPage,
            search: $query,
            publicOnly: true,
        ));
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function summaryFromRow(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'title' => (string) $row['title'],
            'slug' => (string) $row['slug'],
            'description' => isset($row['description']) ? (string) $row['description'] : null,
            'status' => (string) $row['status'],
            'featured' => (bool) ($row['featured'] ?? false),
            'sort_order' => (int) $row['sort_order'],
            'order' => (int) $row['sort_order'],
            'thumbnail' => isset($row['thumbnail_path']) ? (string) $row['thumbnail_path'] : null,
            'cover' => isset($row['cover_path']) ? (string) $row['cover_path'] : null,
            'published_at' => isset($row['published_at']) ? (string) $row['published_at'] : null,
            'views' => (int) ($row['views'] ?? 0),
            'created_at' => isset($row['created_at']) ? (string) $row['created_at'] : null,
            'updated_at' => isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        ];
    }
}
