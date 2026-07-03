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

namespace App\Application\Project\ListProjects;

use App\Application\Project\ProjectPublicCacheInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectStatus;

final class ListProjectsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
        private readonly ProjectPublicCacheInterface $cache,
    ) {
    }

    public function handle(ProjectListFilter $filter): array
    {
        $cacheKey = 'list:' . md5(serialize($filter));
        if ($filter->publicOnly) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $result = $this->projects->paginate($filter);
        $payload = ['data' => $result['items'], 'meta' => [
            'total' => $result['total'],
            'page' => $filter->page,
            'per_page' => $filter->perPage,
        ]];

        if ($filter->publicOnly) {
            $this->cache->set($cacheKey, $payload, 300);
        }

        return $payload;
    }

    public function fromQueryParams(array $params, bool $publicOnly = false): array
    {
        $status = isset($params['status']) ? ProjectStatus::from((string) $params['status']) : null;

        return $this->handle(new ProjectListFilter(
            page: (int) ($params['page'] ?? 1),
            perPage: (int) ($params['per_page'] ?? 15),
            search: isset($params['search']) ? (string) $params['search'] : null,
            status: $status,
            featured: array_key_exists('featured', $params) ? filter_var($params['featured'], FILTER_VALIDATE_BOOLEAN) : null,
            categorySlug: isset($params['category']) ? (string) $params['category'] : null,
            technologySlug: isset($params['technology']) ? (string) $params['technology'] : null,
            tagSlug: isset($params['tag']) ? (string) $params['tag'] : null,
            sort: (string) ($params['sort'] ?? 'sort_order'),
            direction: (string) ($params['direction'] ?? 'asc'),
            publicOnly: $publicOnly,
            withTrashed: ! $publicOnly && filter_var($params['with_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ));
    }
}
