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

namespace App\Controller\Admin;

use App\Application\Acl\CreateRole\CreateRoleCommand;
use App\Application\Acl\CreateRole\CreateRoleHandler;
use App\Application\Acl\DeleteRole\DeleteRoleHandler;
use App\Application\Acl\SyncRolePermissions\SyncRolePermissionsCommand;
use App\Application\Acl\SyncRolePermissions\SyncRolePermissionsHandler;
use App\Application\Acl\SyncUserRoles\SyncUserRolesCommand;
use App\Application\Acl\SyncUserRoles\SyncUserRolesHandler;
use App\Controller\AbstractController;
use App\Domain\Acl\Repository\PermissionRepositoryInterface;
use App\Domain\Acl\Repository\RoleRepositoryInterface;
use App\Http\Request\Admin\CreateRoleRequest;
use App\Http\Request\Admin\SyncRolePermissionsRequest;
use App\Http\Request\Admin\SyncUserRolesRequest;
use Hyperf\Di\Annotation\Inject;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

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

    public function listPermissions(): array
    {
        return ['data' => $this->permissions->all()];
    }

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
