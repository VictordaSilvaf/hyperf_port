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

$auth = [AuthenticateMiddleware::class, RequirePermissionsMiddleware::class];

Router::addGroup('/api/v1', function () use ($auth) {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

    Router::addGroup('/auth', function () {
        Router::post('/register', 'App\Controller\AuthController@register');
        Router::post('/login', 'App\Controller\AuthController@login');
        Router::post('/logout', 'App\Controller\AuthController@logout');
        Router::post('/forgot-password', 'App\Controller\AuthController@forgotPassword');
        Router::post('/reset-password', 'App\Controller\AuthController@resetPassword');

        Router::post('/refresh', 'App\Controller\AuthController@refresh', [
            'middleware' => [AuthenticateMiddleware::class],
        ]);
        Router::post('/change-password', 'App\Controller\AuthController@changePassword', [
            'middleware' => [AuthenticateMiddleware::class],
        ]);
    });

    Router::addGroup('/users', function () {
        Router::get('/me', 'App\Controller\UserController@me', [
            'middleware' => [AuthenticateMiddleware::class],
        ]);
        Router::get('/{id}', 'App\Controller\UserController@show');
    });

    Router::addGroup('/admin', function () use ($auth) {
        Router::get('/users', 'App\Controller\Admin\AdminUserController@index', [
            'middleware' => $auth,
            'permissions' => ['users.view'],
        ]);
        Router::post('/users', 'App\Controller\Admin\AdminUserController@store', [
            'middleware' => $auth,
            'permissions' => ['users.create'],
        ]);
        Router::get('/users/{id}', 'App\Controller\Admin\AdminUserController@show', [
            'middleware' => $auth,
            'permissions' => ['users.view'],
        ]);
        Router::put('/users/{id}', 'App\Controller\Admin\AdminUserController@update', [
            'middleware' => $auth,
            'permissions' => ['users.update'],
        ]);
        Router::get('/roles', 'App\Controller\Admin\RbacController@listRoles', [
            'middleware' => $auth,
            'permissions' => ['roles.view'],
        ]);
        Router::post('/roles', 'App\Controller\Admin\RbacController@createRole', [
            'middleware' => $auth,
            'permissions' => ['roles.create'],
        ]);
        Router::delete('/roles/{id}', 'App\Controller\Admin\RbacController@destroyRole', [
            'middleware' => $auth,
            'permissions' => ['roles.delete'],
        ]);
        Router::put('/roles/{id}/permissions', 'App\Controller\Admin\RbacController@syncRolePermissions', [
            'middleware' => $auth,
            'permissions' => ['roles.assign_permissions'],
        ]);
        Router::get('/permissions', 'App\Controller\Admin\RbacController@listPermissions', [
            'middleware' => $auth,
            'permissions' => ['permissions.view'],
        ]);
        Router::put('/users/{id}/roles', 'App\Controller\Admin\RbacController@syncUserRoles', [
            'middleware' => $auth,
            'permissions' => ['users.assign_roles'],
        ]);
    });
});
