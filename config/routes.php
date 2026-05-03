<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Middleware\AuthenticateMiddleware;
use App\Middleware\RequirePermissionsMiddleware;
use Hyperf\HttpServer\Router\Router;

$authz = [AuthenticateMiddleware::class, RequirePermissionsMiddleware::class];

Router::addGroup('/api/v1', function () use ($authz) {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

    Router::post('/auth/register', 'App\Controller\AuthController@register');
    Router::post('/auth/login', 'App\Controller\AuthController@login');
    Router::post('/auth/logout', 'App\Controller\AuthController@logout');
    Router::post('/auth/forgot-password', 'App\Controller\AuthController@forgotPassword');
    Router::post('/auth/reset-password', 'App\Controller\AuthController@resetPassword');

    Router::post('/auth/refresh', 'App\Controller\AuthController@refresh', ['middleware' => [AuthenticateMiddleware::class]]);
    Router::post('/auth/change-password', 'App\Controller\AuthController@changePassword', ['middleware' => [AuthenticateMiddleware::class]]);

    Router::get('/me', 'App\Controller\UserController@me', ['middleware' => [AuthenticateMiddleware::class]]);
    Router::get('/users/{id}', 'App\Controller\UserController@show');

    Router::get('/admin/roles', 'App\Controller\Admin\RbacController@listRoles', [
        'middleware' => $authz,
        'permissions' => ['roles.view'],
    ]);
    Router::post('/admin/roles', 'App\Controller\Admin\RbacController@createRole', [
        'middleware' => $authz,
        'permissions' => ['roles.create'],
    ]);
    Router::delete('/admin/roles/{id}', 'App\Controller\Admin\RbacController@destroyRole', [
        'middleware' => $authz,
        'permissions' => ['roles.delete'],
    ]);
    Router::put('/admin/roles/{id}/permissions', 'App\Controller\Admin\RbacController@syncRolePermissions', [
        'middleware' => $authz,
        'permissions' => ['roles.assign_permissions'],
    ]);
    Router::get('/admin/permissions', 'App\Controller\Admin\RbacController@listPermissions', [
        'middleware' => $authz,
        'permissions' => ['permissions.view'],
    ]);
    Router::put('/admin/users/{id}/roles', 'App\Controller\Admin\RbacController@syncUserRoles', [
        'middleware' => $authz,
        'permissions' => ['users.assign_roles'],
    ]);
});
