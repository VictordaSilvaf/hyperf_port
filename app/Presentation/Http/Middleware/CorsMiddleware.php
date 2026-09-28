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

namespace App\Presentation\Http\Middleware;

use App\Presentation\Http\Cors\CorsHeaderApplier;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HyperfResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly HyperfResponseInterface $response,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $allowed = CorsHeaderApplier::resolveAllowedOrigin($this->config, $origin);
        $credentials = CorsHeaderApplier::allowCredentials($this->config);

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return CorsHeaderApplier::apply(
                $this->response->raw('')->withStatus(204),
                $allowed,
                $credentials,
            );
        }

        return CorsHeaderApplier::apply($handler->handle($request), $allowed, $credentials);
    }
}
