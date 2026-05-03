<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Acl\EffectivePermissionsProviderInterface;
use App\Application\Auth\AccessTokenIssuerInterface;
use App\Application\Auth\ChangePassword\ChangePasswordCommand;
use App\Application\Auth\ChangePassword\ChangePasswordHandler;
use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\LoginUser\LoginUserCommand;
use App\Application\Auth\LoginUser\LoginUserHandler;
use App\Application\Auth\LoginUser\LoginUserResult;
use App\Application\Auth\RefreshAccessToken\RefreshAccessTokenHandler;
use App\Application\Auth\RequestPasswordReset\RequestPasswordResetCommand;
use App\Application\Auth\RequestPasswordReset\RequestPasswordResetHandler;
use App\Application\Auth\ResetPassword\ResetPasswordCommand;
use App\Application\Auth\ResetPassword\ResetPasswordHandler;
use App\Application\User\RegisterUser\RegisterUserCommand;
use App\Application\User\RegisterUser\RegisterUserHandler;
use App\Domain\User\Exception\EmailAlreadyRegisteredException;
use App\Http\Request\Auth\ChangePasswordRequest;
use App\Http\Request\Auth\ForgotPasswordRequest;
use App\Http\Request\Auth\LoginRequest;
use App\Http\Request\Auth\RegisterRequest;
use App\Http\Request\Auth\ResetPasswordRequest;
use App\Infrastructure\Auth\AuthContext;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

class AuthController extends AbstractController
{
    #[Inject]
    protected AccessTokenIssuerInterface $accessTokens;

    #[Inject]
    protected RegisterUserHandler $registerUser;

    #[Inject]
    protected LoginUserHandler $loginUser;

    #[Inject]
    protected RequestPasswordResetHandler $requestPasswordReset;

    #[Inject]
    protected ResetPasswordHandler $resetPassword;

    #[Inject]
    protected ChangePasswordHandler $changePassword;

    #[Inject]
    protected RefreshAccessTokenHandler $refreshAccessToken;

    #[Inject]
    protected EffectivePermissionsProviderInterface $effectivePermissions;

    public function register(RegisterRequest $request): array|PsrResponseInterface
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
            'access_token' => $this->accessTokens->issue($userId),
            'token_type' => 'Bearer',
            'message' => trans('http.registration_successful'),
            'roles' => $this->effectivePermissions->roleSlugsForUser($userId),
            'permissions' => $this->effectivePermissions->permissionSlugsForUser($userId),
        ];
    }

    public function login(LoginRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $result = $this->loginUser->handle(new LoginUserCommand(
                (string) $data['email'],
                (string) $data['password'],
            ));
        } catch (InvalidCredentialsException) {
            return $this->response->json([
                'message' => trans('http.invalid_email_or_password'),
            ])->withStatus(401);
        }

        return $this->loginResultToArray($result);
    }

    public function logout(): array
    {
        return [
            'message' => trans('http.logout_stateless'),
        ];
    }

    public function refresh(): array|PsrResponseInterface
    {
        $userId = AuthContext::userId();
        if ($userId === null) {
            return $this->response->json(['message' => trans('http.unauthorized')])->withStatus(401);
        }

        try {
            $token = $this->refreshAccessToken->handle($userId);
        } catch (InvalidCredentialsException) {
            return $this->response->json(['message' => trans('http.unauthorized')])->withStatus(401);
        }

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'roles' => $this->effectivePermissions->roleSlugsForUser($userId),
            'permissions' => $this->effectivePermissions->permissionSlugsForUser($userId),
        ];
    }

    public function forgotPassword(ForgotPasswordRequest $request): array
    {
        $data = $request->validated();
        $this->requestPasswordReset->handle(new RequestPasswordResetCommand((string) $data['email']));

        return [
            'message' => trans('http.forgot_password_generic'),
        ];
    }

    public function resetPassword(ResetPasswordRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $this->resetPassword->handle(new ResetPasswordCommand(
                (string) $data['code'],
                (string) $data['password'],
            ));
        } catch (InvalidCredentialsException) {
            return $this->response->json([
                'message' => trans('http.reset_invalid_code'),
            ])->withStatus(422);
        }

        return [
            'message' => trans('http.reset_success'),
        ];
    }

    public function changePassword(ChangePasswordRequest $request): array|PsrResponseInterface
    {
        $userId = AuthContext::userId();
        if ($userId === null) {
            return $this->response->json(['message' => trans('http.unauthorized')])->withStatus(401);
        }

        $data = $request->validated();
        try {
            $this->changePassword->handle(new ChangePasswordCommand(
                $userId,
                (string) $data['current_password'],
                (string) $data['password'],
            ));
        } catch (InvalidCredentialsException) {
            return $this->response->json([
                'message' => trans('http.current_password_incorrect'),
            ])->withStatus(401);
        }

        return [
            'message' => trans('http.password_updated'),
        ];
    }

    /**
     * @return array{access_token: string, token_type: string, roles: list<string>, permissions: list<string>}
     */
    private function loginResultToArray(LoginUserResult $result): array
    {
        return [
            'access_token' => $result->accessToken,
            'token_type' => 'Bearer',
            'roles' => $result->roleSlugs,
            'permissions' => $result->permissionSlugs,
        ];
    }
}
