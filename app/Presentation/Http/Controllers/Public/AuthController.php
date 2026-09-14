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
use App\Infrastructure\Auth\AuthContext;
use App\Presentation\Http\Controllers\AbstractController;
use App\Presentation\Http\OpenApi\OpenApiRefs;
use App\Presentation\Http\Requests\Public\Auth\ChangePasswordRequest;
use App\Presentation\Http\Requests\Public\Auth\ForgotPasswordRequest;
use App\Presentation\Http\Requests\Public\Auth\LoginRequest;
use App\Presentation\Http\Requests\Public\Auth\RegisterRequest;
use App\Presentation\Http\Requests\Public\Auth\ResetPasswordRequest;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation as SA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Translation\trans;

#[SA\HyperfServer('openapi')]
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

    #[SA\Post(path: '/api/v1/auth/register', summary: 'Registar utilizador', tags: ['Auth'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/RegisterRequest'))]
    #[SA\Response(response: 200, description: 'Registo OK', content: new SA\JsonContent(ref: '#/components/schemas/AuthTokenResponse'))]
    #[SA\Response(response: 409, description: 'E-mail já registado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Post(path: '/api/v1/auth/login', summary: 'Login', tags: ['Auth'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/LoginRequest'))]
    #[SA\Response(response: 200, description: 'Login OK', content: new SA\JsonContent(ref: '#/components/schemas/AuthTokenResponse'))]
    #[SA\Response(response: 401, description: 'Credenciais inválidas', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Post(path: '/api/v1/auth/logout', summary: 'Logout (stateless)', tags: ['Auth'])]
    #[SA\Response(response: 200, description: 'Instrução ao cliente', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    public function logout(): array
    {
        return [
            'message' => trans('http.logout_stateless'),
        ];
    }

    #[SA\Post(path: '/api/v1/auth/refresh', summary: 'Refresh access token', security: OpenApiRefs::BEARER, tags: ['Auth'])]
    #[SA\Response(response: 200, description: 'Novo token', content: new SA\JsonContent(ref: '#/components/schemas/AuthTokenResponse'))]
    #[SA\Response(response: 401, description: 'Não autenticado', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
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

    #[SA\Post(path: '/api/v1/auth/forgot-password', summary: 'Pedir reset de password', tags: ['Auth'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest'))]
    #[SA\Response(response: 200, description: 'Mensagem genérica', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
    public function forgotPassword(ForgotPasswordRequest $request): array
    {
        $data = $request->validated();
        $this->requestPasswordReset->handle(new RequestPasswordResetCommand((string) $data['email']));

        return [
            'message' => trans('http.forgot_password_generic'),
        ];
    }

    #[SA\Post(path: '/api/v1/auth/reset-password', summary: 'Reset password com código', tags: ['Auth'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest'))]
    #[SA\Response(response: 200, description: 'Password actualizada', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 422, description: 'Código inválido ou validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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

    #[SA\Post(path: '/api/v1/auth/change-password', summary: 'Alterar password', security: OpenApiRefs::BEARER, tags: ['Auth'])]
    #[SA\RequestBody(required: true, content: new SA\JsonContent(ref: '#/components/schemas/ChangePasswordRequest'))]
    #[SA\Response(response: 200, description: 'Password actualizada', content: new SA\JsonContent(ref: OpenApiRefs::MESSAGE))]
    #[SA\Response(response: 401, description: 'Não autenticado ou password actual incorrecta', content: new SA\JsonContent(ref: OpenApiRefs::ERR))]
    #[SA\Response(response: 422, description: 'Validação', content: new SA\JsonContent(ref: OpenApiRefs::VALIDATION))]
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
