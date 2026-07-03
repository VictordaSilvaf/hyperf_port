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

namespace App\Application\Page\GetPage;

use App\Application\Page\Shared\PagePresenter;
use App\Domain\Page\Exception\PageNotFoundException;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\Page\ValueObject\PageId;

final class GetPageHandler
{
    public function __construct(
        private readonly PageRepositoryInterface $pages,
        private readonly PagePresenter $presenter,
    ) {
    }

    public function handle(GetPageQuery $query): array
    {
        $id = PageId::fromString($query->pageId);
        $page = $this->pages->findById($id, $query->withTrashed);
        if ($page === null) {
            throw PageNotFoundException::withId($query->pageId);
        }

        return ['data' => $this->presenter->toDetail($page)];
    }
}
