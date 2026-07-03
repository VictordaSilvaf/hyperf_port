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

use App\Application\Acl\EffectivePermissionsProviderInterface;
use App\Application\User\GetUser\GetUserHandler;
use App\Application\User\GetUser\GetUserQuery;
use App\Application\User\ListUsers\ListUsersHandler;
use App\Application\User\ListUsers\ListUsersQuery;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Application\User\UpdateUser\UpdateUserCommand;
use App\Application\User\UpdateUser\UpdateUserHandler;
use App\Controller\AbstractController;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Http\Request\Admin\CreateAdminUserRequest;
use App\Http\Request\Admin\ListAdminUsersRequest;
use App\Http\Request\Admin\UpdateAdminUserRequest;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

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

    public function index(ListAdminUsersRequest $request): array
    {
        $data = $request->validated();
        $page = (int) ($data['page'] ?? 1);
        $perPage = (int) ($data['per_page'] ?? 15);
        $search = isset($data['search']) ? (string) $data['search'] : null;

        return $this->listUsers->handle(new ListUsersQuery($page, $perPage, $search));
    }

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
