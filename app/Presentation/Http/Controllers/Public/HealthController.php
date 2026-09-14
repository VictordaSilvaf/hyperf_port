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
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[SA\HyperfServer('openapi')]
final class HealthController extends AbstractController
{
    #[Inject]
    protected GetHealthHandler $getHealth;

    #[SA\Get(path: '/api/v1/health/live', summary: 'Liveness probe', tags: ['Health'])]
    #[SA\Response(response: 200, description: 'Processo HTTP activo', content: new SA\JsonContent(ref: '#/components/schemas/HealthStatus'))]
    public function live(): PsrResponseInterface
    {
        return $this->respond(GetHealthQuery::MODE_LIVE);
    }

    #[SA\Get(path: '/api/v1/health/ready', summary: 'Readiness probe', tags: ['Health'])]
    #[SA\Response(response: 200, description: 'Pronto para tráfego', content: new SA\JsonContent(ref: '#/components/schemas/HealthStatus'))]
    #[SA\Response(response: 503, description: 'Dependência falhou', content: new SA\JsonContent(ref: '#/components/schemas/HealthStatus'))]
    public function ready(): PsrResponseInterface
    {
        return $this->respond(GetHealthQuery::MODE_READY);
    }

    /** Aggregate health (same checks as readiness — for load balancers / monitoring). */
    #[SA\Get(path: '/api/v1/health', summary: 'Health aggregate (alias de readiness)', tags: ['Health'])]
    #[SA\Response(response: 200, description: 'OK', content: new SA\JsonContent(ref: '#/components/schemas/HealthStatus'))]
    #[SA\Response(response: 503, description: 'Dependência falhou', content: new SA\JsonContent(ref: '#/components/schemas/HealthStatus'))]
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
