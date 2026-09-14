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

namespace App\Presentation\Http\OpenApi\Schemas;

use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(
    schema: 'RegisterRequest',
    type: 'object',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new SA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 100, example: 'Victor Fernandes'),
        new SA\Property(property: 'email', type: 'string', format: 'email', example: 'victor@exemplo.com'),
        new SA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'Secret123'),
        new SA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Secret123'),
    ]
)]
#[SA\Schema(
    schema: 'LoginRequest',
    type: 'object',
    required: ['email', 'password'],
    properties: [
        new SA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@victordev.com'),
        new SA\Property(property: 'password', type: 'string', format: 'password', example: 'VictorDev123!'),
    ]
)]
#[SA\Schema(
    schema: 'ForgotPasswordRequest',
    type: 'object',
    required: ['email'],
    properties: [
        new SA\Property(property: 'email', type: 'string', format: 'email'),
    ]
)]
#[SA\Schema(
    schema: 'ResetPasswordRequest',
    type: 'object',
    required: ['code', 'password', 'password_confirmation'],
    properties: [
        new SA\Property(property: 'code', type: 'string', pattern: '^\d{6}$', example: '123456'),
        new SA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new SA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
#[SA\Schema(
    schema: 'ChangePasswordRequest',
    type: 'object',
    required: ['current_password', 'password', 'password_confirmation'],
    properties: [
        new SA\Property(property: 'current_password', type: 'string', format: 'password'),
        new SA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new SA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
#[SA\Schema(
    schema: 'SubmitContactRequest',
    type: 'object',
    required: ['name', 'email', 'message'],
    properties: [
        new SA\Property(property: 'name', type: 'string', maxLength: 200, example: 'João Silva'),
        new SA\Property(property: 'email', type: 'string', format: 'email'),
        new SA\Property(property: 'subject', type: 'string', maxLength: 300, nullable: true),
        new SA\Property(property: 'message', type: 'string', minLength: 10, maxLength: 5000),
        new SA\Property(property: 'website', type: 'string', description: 'Honeypot — must be absent', nullable: true),
        new SA\Property(property: 'cf_turnstile_response', type: 'string', nullable: true),
    ]
)]
final class AuthRequests
{
}
