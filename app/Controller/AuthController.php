<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Auth\AccessTokenIssuerInterface;
use App\Application\Auth\ChangePassword\ChangePasswordCommand;
use App\Application\Auth\ChangePassword\ChangePasswordHandler;
use App\Application\Auth\InvalidCredentialsException;
use App\Application\Auth\LoginUser\LoginUserCommand;
use App\Application\Auth\LoginUser\LoginUserHandler;
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

    public function register(RegisterRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $userId = $this->registerUser->handle(new RegisterUserCommand(
                (string) $data['name'],
                (string) $data['email'],
                (string) $data['password'],
            ));
        } catch (EmailAlreadyRegisteredException $e) {
            return $this->response->json([
                'message' => $e->getMessage(),
            ])->withStatus(409);
        }

        return [
            'id' => $userId,
            'access_token' => $this->accessTokens->issue($userId),
            'token_type' => 'Bearer',
            'message' => 'Registration successful.',
        ];
    }

    public function login(LoginRequest $request): array|PsrResponseInterface
    {
        $data = $request->validated();
        try {
            $token = $this->loginUser->handle(new LoginUserCommand(
                (string) $data['email'],
                (string) $data['password'],
            ));
        } catch (InvalidCredentialsException) {
            return $this->response->json([
                'message' => 'Invalid email or password.',
            ])->withStatus(401);
        }

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(): array
    {
        return [
            'message' => 'Token discarded on the client. This endpoint is a no-op for stateless APIs.',
        ];
    }

    public function refresh(): array|PsrResponseInterface
    {
        $userId = AuthContext::userId();
        if ($userId === null) {
            return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
        }

        try {
            $token = $this->refreshAccessToken->handle($userId);
        } catch (InvalidCredentialsException) {
            return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
        }

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function forgotPassword(ForgotPasswordRequest $request): array
    {
        $data = $request->validated();
        $this->requestPasswordReset->handle(new RequestPasswordResetCommand((string) $data['email']));

        return [
            'message' => 'If an account exists for that email, password reset instructions have been sent.',
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
                'message' => 'Invalid or expired verification code.',
            ])->withStatus(422);
        }

        return [
            'message' => 'Password has been reset. You can sign in with your new password.',
        ];
    }

    public function changePassword(ChangePasswordRequest $request): array|PsrResponseInterface
    {
        $userId = AuthContext::userId();
        if ($userId === null) {
            return $this->response->json(['message' => 'Unauthorized'])->withStatus(401);
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
                'message' => 'Current password is incorrect.',
            ])->withStatus(401);
        }

        return [
            'message' => 'Password updated.',
        ];
    }
}
