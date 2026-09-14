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

namespace App\Presentation\Http\Controllers\Admin;

use App\Application\Acl\EffectivePermissionsProviderInterface;
use App\Application\User\GetUser\GetUserHandler;
use App\Application\User\GetUser\GetUserQuery;
use App\Application\User\ListUsers\ListUsersHandler;
use App\Application\User\ListUsers\ListUsersQuery;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Application\User\UpdateUser\UpdateUserCommand;
use App\Application\User\UpdateUser\UpdateUserHandler;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\CreateAdminUserRequest;
use App\Presentation\Http\Requests\Admin\ListAdminUsersRequest;
use App\Presentation\Http\Requests\Admin\UpdateAdminUserRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class AdminUserController extends AbstractController
{
    #[Inject]
    protected ListUsersHandler $listUsers;

    #[Inject]
    protected GetUserHandler $getUser;

    #[Inject]
    protected RegisterUserHandler $registerUser;

    #[Inject]
    protected UpdateUserHandler $updateUser;

    #[Inject]
    protected EffectivePermissionsProviderInterface $effectivePermissions;

    #[SA\Get(path: '/api/v1/admin/users', summary: 'Listar utilizadores', description: 'Requires permission: users.view', security: OpenApiRefs::BEARER, tags: ['Admin Users'])]
    #[SA\QueryParameter(name: 'page', required: false, schema: new SA\Schema(type: 'integer', default: 1))]
    #[SA\QueryParameter(name: 'per_page', required: false, schema: new SA\Schema(type: 'integer', default: 15))]
    #[SA\QueryParameter(name: 'search', required: false, schema: new SA\Schema(type: 'string'))]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/AdminUserList'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function index(ListAdminUsersRequest $request): array
    {
        $data = $request->validated();
        $page = (int) ($data['page'] ?? 1);
        $perPage = (int) ($data['per_page'] ?? 15);
        $search = isset($data['search']) ? (string) $data['search'] : null;

        return $this->listUsers->handle(new ListUsersQuery($page, $perPage, $search));
    }

    #[SA\Get(path: '/api/v1/admin/users/{id}', summary: 'Obter utilizador', description: 'Requires permission: users.view', security: OpenApiRefs::BEARER, tags: ['Admin Users'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Perfil com roles e permissions', content: new SA\JsonContent(ref: '#/components/schemas/UserProfile'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $user = $this->getUser->handle(new GetUserQuery($id));
        } catch (UserNotFoundException) {
            return $this->response->json(['message' => trans('http.user_not_found')])->withStatus(404);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $this->effectivePermissions->roleSlugsForUser($id),
            'permissions' => $this->effectivePermissions->permissionSlugsForUser($id),
        ];
    }

    #[SA\Post(path: '/api/v1/admin/users', summary: 'Criar utilizador', description: 'Requires permission: users.create', security: OpenApiRefs::BEARER, tags: ['Admin Users'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreateAdminUserRequest'))]
    #[SA\Response(response: 200, description: 'Utilizador criado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'E-mail já registado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function store(CreateAdminUserRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $userId = $this->registerUser->handle(new RegisterUserCommand(
                (string) $data['name'],
                (string) $data['email'],
                (string) $data['password'],
            ));
        } catch (EmailAlreadyRegisteredException) {
            return $this->response->json([
                'message' => trans('http.email_already_registered'),
            ])->withStatus(409);
        }

        return [
            'id' => $userId,
            'message' => trans('http.admin_user_created'),
        ];
    }

    #[SA\Put(path: '/api/v1/admin/users/{id}', summary: 'Actualizar utilizador', description: 'Requires permission: users.update', security: OpenApiRefs::BEARER, tags: ['Admin Users'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/UpdateAdminUserRequest'))]
    #[SA\Response(response: 200, description: 'Actualizado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'E-mail já registado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function update(string $id, UpdateAdminUserRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->updateUser->handle(new UpdateUserCommand(
                $id,
                (string) $data['name'],
                (string) $data['email'],
            ));
        } catch (UserNotFoundException) {
            return $this->response->json(['message' => trans('http.user_not_found')])->withStatus(404);
        } catch (EmailAlreadyRegisteredException) {
            return $this->response->json([
                'message' => trans('http.email_already_registered'),
            ])->withStatus(409);
        }

        return ['message' => trans('http.admin_user_updated')];
    }
}
