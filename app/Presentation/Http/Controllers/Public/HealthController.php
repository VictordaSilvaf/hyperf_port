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

use App\Application\Health\GetHealth\GetHealthHandler;
use App\Application\Health\GetHealth\GetHealthQuery;
use App\Presentation\Http\Controllers\AbstractController;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

final class HealthController extends AbstractController
{
    #[Inject]
    protected GetHealthHandler $getHealth;

    public function live(): PsrResponseInterface
    {
        return $this->respond(GetHealthQuery::MODE_LIVE);
    }

    public function ready(): PsrResponseInterface
    {
        return $this->respond(GetHealthQuery::MODE_READY);
    }

    /** Aggregate health (same checks as readiness — for load balancers / monitoring). */
    public function index(): PsrResponseInterface
    {
        return $this->respond(GetHealthQuery::MODE_READY);
    }

    private function respond(string $mode): PsrResponseInterface
    {
        $result = $this->getHealth->handle(new GetHealthQuery($mode));

        return $this->response->json($result->toArray())->withStatus($result->httpStatusCode());
    }
}
