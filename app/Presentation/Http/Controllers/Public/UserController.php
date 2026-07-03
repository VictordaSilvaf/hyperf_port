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
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

class UserController extends AbstractController
{
    #[Inject]
    protected GetUserHandler $getUser;

    #[Inject]
    protected EffectivePermissionsProviderInterface $effectivePermissions;

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
