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
use App\Middleware\AuthenticateMiddleware;
use App\Middleware\RequirePermissionsMiddleware;
use Hyperf\HttpServer\Router\Router;

$auth = [AuthenticateMiddleware::class, RequirePermissionsMiddleware::class];

Router::addGroup('/api/v1', function () use ($auth) {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

    Router::addGroup('/health', function () {
        Router::get('', 'App\Controller\HealthController@index');
        Router::get('/live', 'App\Controller\HealthController@live');
        Router::get('/ready', 'App\Controller\HealthController@ready');
    });

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
        Router::addGroup('/users', function () use ($auth) {
            Router::get('/', 'App\Controller\Admin\AdminUserController@index', [
                'middleware' => $auth,
                'permissions' => ['users.view'],
            ]);
            Router::post('/', 'App\Controller\Admin\AdminUserController@store', [
                'middleware' => $auth,
                'permissions' => ['users.create'],
            ]);
            Router::get('/{id}', 'App\Controller\Admin\AdminUserController@show', [
                'middleware' => $auth,
                'permissions' => ['users.view'],
            ]);
            Router::put('/{id}', 'App\Controller\Admin\AdminUserController@update', [
                'middleware' => $auth,
                'permissions' => ['users.update'],
            ]);
        });

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
