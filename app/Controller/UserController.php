<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\User\GetUser\GetUserHandler;
use App\Application\User\GetUser\GetUserQuery;
use App\Domain\User\Exception\UserNotFoundException;
use App\Infrastructure\Auth\AuthContext;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class UserController extends AbstractController
{
    #[Inject]
    protected GetUserHandler $getUser;

    public function me(): array|PsrResponseInterface
    {
        $userId = AuthContext::userId();
        if ($userId === null) {
            return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
        }

        try {
            $result = $this->getUser->handle(new GetUserQuery($userId));
        } catch (UserNotFoundException) {
            return $this->response->json(['message' => 'User not found'])->withStatus(404);
        }

        return [
            'id' => $result->id,
            'name' => $result->name,
            'email' => $result->email,
        ];
    }

    public function show(string $id): array|PsrResponseInterface
    {
        try {
            $result = $this->getUser->handle(new GetUserQuery($id));
        } catch (UserNotFoundException) {
            return $this->response->json(['message' => 'User not found'])->withStatus(404);
        }

        return [
            'id' => $result->id,
            'name' => $result->name,
            'email' => $result->email,
        ];
    }
}
