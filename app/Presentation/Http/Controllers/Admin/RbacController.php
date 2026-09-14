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

use App\Application\Acl\CreateRole\CreateRoleCommand;
use App\Application\Acl\CreateRole\CreateRoleHandler;
use App\Application\Acl\DeleteRole\DeleteRoleHandler;
use App\Application\Acl\SyncRolePermissions\SyncRolePermissionsCommand;
use App\Application\Acl\SyncRolePermissions\SyncRolePermissionsHandler;
use App\Application\Acl\SyncUserRoles\SyncUserRolesCommand;
use App\Application\Acl\SyncUserRoles\SyncUserRolesHandler;
use App\Domain\Acl\Repository\PermissionRepositoryInterface;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Admin\CreateRoleRequest;
use App\Presentation\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Presentation\Http\Requests\Admin\SyncUserRolesRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
final class RbacController extends AbstractController
{
    #[Inject]
    protected RoleRepositoryInterface $roles;

    #[Inject]
    protected PermissionRepositoryInterface $permissions;

    #[Inject]
    protected CreateRoleHandler $createRoleHandler;

    #[Inject]
    protected DeleteRoleHandler $deleteRoleHandler;

    #[Inject]
    protected SyncRolePermissionsHandler $syncRolePermissionsHandler;

    #[Inject]
    protected SyncUserRolesHandler $syncUserRolesHandler;

    #[SA\Get(path: '/api/v1/admin/roles', summary: 'Listar roles', description: 'Requires permission: roles.view', security: OpenApiRefs::BEARER, tags: ['Admin RBAC'])]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/RoleList'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function listRoles(): array
    {
        $out = [];
        foreach ($this->roles->all() as $r) {
            $out[] = [
                'id' => $r->id(),
                'slug' => $r->slug(),
                'name' => $r->name(),
                'is_system' => $r->isSystem(),
            ];
        }

        return ['data' => $out];
    }

    #[SA\Post(path: '/api/v1/admin/roles', summary: 'Criar role', description: 'Requires permission: roles.create', security: OpenApiRefs::BEARER, tags: ['Admin RBAC'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/CreateRoleRequest'))]
    #[SA\Response(response: 200, description: 'Role criada', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 409, description: 'Slug já existe', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function createRole(CreateRoleRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $id = $this->createRoleHandler->handle(new CreateRoleCommand((string) $data['name'], (string) $data['slug']));
        } catch (InvalidArgumentException $e) {
            return $this->response->json(['message' => $e->getMessage()])->withStatus(409);
        }

        return ['id' => $id, 'message' => trans('http.rbac_role_created')];
    }

    #[SA\Delete(path: '/api/v1/admin/roles/{id}', summary: 'Eliminar role', description: 'Requires permission: roles.delete', security: OpenApiRefs::BEARER, tags: ['Admin RBAC'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\Response(response: 200, description: 'Eliminada', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Role de sistema ou inválida', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function destroyRole(string $id): array|PsrResponseInterface
    {
        try {
            $this->deleteRoleHandler->handle($id);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return $this->response->json(['message' => $e->getMessage()])->withStatus($status);
        }

        return ['message' => trans('http.rbac_role_deleted')];
    }

    #[SA\Put(path: '/api/v1/admin/roles/{id}/permissions', summary: 'Sincronizar permissions da role', description: 'Requires permission: roles.assign_permissions', security: OpenApiRefs::BEARER, tags: ['Admin RBAC'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SyncRolePermissionsRequest'))]
    #[SA\Response(response: 200, description: 'Actualizado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrada', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function syncRolePermissions(string $id, SyncRolePermissionsRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->syncRolePermissionsHandler->handle(new SyncRolePermissionsCommand($id, array_values($data['permission_slugs'])));
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return $this->response->json(['message' => $e->getMessage()])->withStatus($status);
        }

        return ['message' => trans('http.rbac_role_permissions_updated')];
    }

    #[SA\Get(path: '/api/v1/admin/permissions', summary: 'Listar permissions', description: 'Requires permission: permissions.view', security: OpenApiRefs::BEARER, tags: ['Admin RBAC'])]
    #[SA\Response(response: 200, description: 'Lista', content: new SA\JsonContent(ref: '#/components/schemas/PermissionList'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    public function listPermissions(): array
    {
        return ['data' => $this->permissions->all()];
    }

    #[SA\Put(path: '/api/v1/admin/users/{id}/roles', summary: 'Sincronizar roles do utilizador', description: 'Requires permission: users.assign_roles', security: OpenApiRefs::BEARER, tags: ['Admin RBAC'])]
    #[SA\PathParameter(name: 'id', required: true, schema: new SA\Schema(type: 'string', format: 'uuid'))]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/SyncUserRolesRequest'))]
    #[SA\Response(response: 200, description: 'Actualizado', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 403, description: 'Sem permissão', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 404, description: 'Não encontrado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function syncUserRoles(string $userId, SyncUserRolesRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->syncUserRolesHandler->handle(new SyncUserRolesCommand($userId, array_values($data['role_slugs'])));
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return $this->response->json(['message' => $e->getMessage()])->withStatus($status);
        }

        return ['message' => trans('http.rbac_user_roles_updated')];
    }
}
