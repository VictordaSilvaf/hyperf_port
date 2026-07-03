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

use App\Application\Page\ListBlockTypes\ListBlockTypesHandler;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Di\Annotation\Inject;

final class BlockTypeController extends AbstractController
{
    #[Inject]
    protected ListBlockTypesHandler $listBlockTypes;

    public function index(): array
    {
        return $this->listBlockTypes->handle();
    }
}
