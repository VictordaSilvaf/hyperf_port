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
return [
    'forbidden' => 'Forbidden',
    'validation_failed' => 'Validation failed',
    'unauthorized' => 'Unauthorized',
    'internal_server_error' => 'Internal Server Error.',
    'email_already_registered' => 'An account with this email already exists.',
    'registration_successful' => 'Registration successful.',
    'invalid_email_or_password' => 'Invalid email or password.',
    'logout_stateless' => 'Token discarded on the client. This endpoint is a no-op for stateless APIs.',
    'forgot_password_generic' => 'If an account exists for that email, password reset instructions have been sent.',
    'reset_invalid_code' => 'Invalid or expired verification code.',
    'reset_success' => 'Password has been reset. You can sign in with your new password.',
    'current_password_incorrect' => 'Current password is incorrect.',
    'password_updated' => 'Password updated.',
    'user_not_found' => 'User not found',
    'rbac_role_created' => 'Role created.',
    'rbac_role_deleted' => 'Role deleted.',
    'rbac_role_permissions_updated' => 'Role permissions updated.',
    'rbac_user_roles_updated' => 'User roles updated.',
    'admin_user_created' => 'User created.',
    'admin_user_updated' => 'User updated.',
];
