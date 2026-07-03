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

namespace App\Middleware;

use App\Infrastructure\Auth\AuthContext;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Translation\trans;

final class RequirePermissionsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        $required = [];
        if ($dispatched instanceof Dispatched && $dispatched->handler instanceof Handler) {
            $opt = $dispatched->handler->options['permissions'] ?? null;
            if (is_array($opt)) {
                $required = $opt;
            }
        }

        if ($required === []) {
            return $handler->handle($request);
        }

        foreach ($required as $slug) {
            if (! is_string($slug) || ! AuthContext::can($slug)) {
                return $this->response->json(['message' => trans('http.forbidden')])->withStatus(403);
            }
        }

        return $handler->handle($request);
    }
}
