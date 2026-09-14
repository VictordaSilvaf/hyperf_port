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

use App\Application\Acl\EffectivePermissionsProviderInterface;
use App\Application\User\GetUser\GetUserHandler;
use App\Application\User\GetUser\GetUserQuery;
use App\Domain\User\Exception\UserNotFoundException;
use App\Infrastructure\Auth\AuthContext;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
class UserController extends AbstractController
{
    #[Inject]
    protected GetUserHandler $getUser;

    #[Inject]
    protected EffectivePermissionsProviderInterface $effectivePermissions;

    #[SA\Get(path: '/api/v1/users/me', summary: 'Utilizador autenticado', security: OpenApiRefs::BEARER, tags: ['Users'])]
    #[SA\Response(response: 200, description: 'Perfil', content: new SA\JsonContent(ref: '#/components/schemas/UserProfile'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Utilizador não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function me(): array|PsrResponseInterface
    {
        $userId = AuthContext::userId();
        if ($userId === null) {
            return $this->response->json(['message' => trans('http.unauthorized')])->withStatus(401);
        }

        try {
            $result = $this->getUser->handle(new GetUserQuery($userId));
        } catch (UserNotFoundException) {
            return $this->response->json(['message' => trans('http.user_not_found')])->withStatus(404);
        }

        return [
            'id' => $result->id,
            'name' => $result->name,
            'email' => $result->email,
            'roles' => $this->effectivePermissions->roleSlugsForUser($userId),
            'permissions' => $this->effectivePermissions->permissionSlugsForUser($userId),
        ];
    }

    #[SA\Get(path: '/api/v1/users/{id}', summary: 'Utilizador por ID (público)', tags: ['Users'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Utilizador', content: new SA\JsonContent(ref: '#/components/schemas/UserPublic'))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $result = $this->getUser->handle(new GetUserQuery($id));
        } catch (UserNotFoundException) {
            return $this->response->json(['message' => trans('http.user_not_found')])->withStatus(404);
        }

        return [
            'id' => $result->id,
            'name' => $result->name,
            'email' => $result->email,
        ];
    }
}
