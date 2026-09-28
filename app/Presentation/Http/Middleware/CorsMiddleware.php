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

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HyperfResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    /** @var list<string> */
    private readonly array $origins;

    private readonly bool $allowCredentials;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly HyperfResponseInterface $response,
    ) {
        $raw = (string) $this->config->get('cors.origins', '');
        $this->origins = array_values(array_filter(array_map(
            static fn (string $o): string => trim($o),
            explode(',', $raw),
        ), static fn (string $o): bool => $o !== ''));
        $this->allowCredentials = (bool) $this->config->get('cors.allow_credentials', false);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigin = $this->resolveAllowedOrigin($origin);

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $this->withCorsHeaders($this->response->raw('')->withStatus(204), $allowedOrigin);
        }

        return $this->withCorsHeaders($handler->handle($request), $allowedOrigin);
    }

    private function resolveAllowedOrigin(string $origin): ?string
    {
        if ($origin === '' || $this->origins === []) {
            return null;
        }

        foreach ($this->origins as $allowed) {
            if (strcasecmp($allowed, $origin) === 0) {
                return $origin;
            }
        }

        return null;
    }

    private function withCorsHeaders(ResponseInterface $response, ?string $allowedOrigin): ResponseInterface
    {
        if ($allowedOrigin === null) {
            return $response;
        }

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin')
            ->withHeader('Access-Control-Max-Age', '86400')
            ->withHeader('Vary', 'Origin');

        if ($this->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
