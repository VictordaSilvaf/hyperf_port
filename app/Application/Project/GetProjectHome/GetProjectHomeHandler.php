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

namespace App\Application\Project\GetProjectHome;

use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Project\ValueObject\ProjectListFilter;
use App\Domain\Project\ValueObject\ProjectStatus;

final class GetProjectHomeHandler
{
    public function __construct(private readonly ProjectRepositoryInterface $projects)
    {
    }

    public function handle(): array
    {
        $featured = $this->projects->paginate(new ProjectListFilter(
            page: 1,
            perPage: 6,
            featured: true,
            publicOnly: true,
            sort: 'sort_order',
        ));

        $latest = $this->projects->paginate(new ProjectListFilter(
            page: 1,
            perPage: 6,
            status: ProjectStatus::Published,
            publicOnly: true,
            sort: 'published_at',
            direction: 'desc',
        ));

        return [
            'featured' => $featured['items'],
            'latest' => $latest['items'],
        ];
    }
}
