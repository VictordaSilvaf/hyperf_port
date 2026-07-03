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

use App\Application\Acl\EffectivePermissionsProviderInterface;
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
        private readonly EffectivePermissionsProviderInterface $effectivePermissions,
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
        AuthContext::setPermissionSlugs($this->effectivePermissions->permissionSlugsForUser($userId));

        return $handler->handle($request);
    }

    private function unauthorized(): PsrResponseInterface
    {
        return $this->response->json(['message' => trans('http.unauthorized')])->withStatus(401);
    }
}
