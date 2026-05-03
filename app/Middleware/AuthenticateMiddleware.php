<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Infrastructure\Auth\AuthContext;
use App\Infrastructure\Auth\SignedAccessTokenIssuer;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Translation\trans;

class AuthenticateMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SignedAccessTokenIssuer $accessTokens,
        private readonly ResponseInterface $response,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (! preg_match('/^\s*Bearer\s+(\S+)\s*$/i', $header, $matches)) {
            return $this->unauthorized();
        }

        $userId = $this->accessTokens->parseUserId($matches[1]);
        if ($userId === null) {
            return $this->unauthorized();
        }

        AuthContext::setUserId($userId);

        return $handler->handle($request);
    }

    private function unauthorized(): PsrResponseInterface
    {
        return $this->response->json(['message' => trans('http.unauthorized')])->withStatus(401);
    }
}
