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
use App\Presentation\Http\Middleware\AuthenticateMiddleware;
use App\Presentation\Http\Middleware\RequirePermissionsMiddleware;
use Hyperf\HttpServer\Router\Router;

$auth = [AuthenticateMiddleware::class, RequirePermissionsMiddleware::class];

Router::addGroup('/api/v1', function () use ($auth) {
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Presentation\Http\Controllers\Public\IndexController@index');

    Router::addGroup('/health', function () {
        Router::get('', 'App\Presentation\Http\Controllers\Public\HealthController@index');
        Router::get('/live', 'App\Presentation\Http\Controllers\Public\HealthController@live');
        Router::get('/ready', 'App\Presentation\Http\Controllers\Public\HealthController@ready');
    });

    Router::addGroup('/auth', function () {
        Router::post('/register', 'App\Presentation\Http\Controllers\Public\AuthController@register');
        Router::post('/login', 'App\Presentation\Http\Controllers\Public\AuthController@login');
        Router::post('/logout', 'App\Presentation\Http\Controllers\Public\AuthController@logout');
        Router::post('/forgot-password', 'App\Presentation\Http\Controllers\Public\AuthController@forgotPassword');
        Router::post('/reset-password', 'App\Presentation\Http\Controllers\Public\AuthController@resetPassword');

        Router::post('/refresh', 'App\Presentation\Http\Controllers\Public\AuthController@refresh', [
            'middleware' => [AuthenticateMiddleware::class],
        ]);
        Router::post('/change-password', 'App\Presentation\Http\Controllers\Public\AuthController@changePassword', [
            'middleware' => [AuthenticateMiddleware::class],
        ]);
    });

    Router::addGroup('/users', function () {
        Router::get('/me', 'App\Presentation\Http\Controllers\Public\UserController@me', [
            'middleware' => [AuthenticateMiddleware::class],
        ]);
        Router::get('/{id}', 'App\Presentation\Http\Controllers\Public\UserController@show');
    });

    Router::addGroup('/projects', function () {
        Router::get('/', 'App\Presentation\Http\Controllers\Public\ProjectController@index');
        Router::get('/{projectId}/posts', 'App\Presentation\Http\Controllers\Public\PostController@index');
        Router::get('/{slug}', 'App\Presentation\Http\Controllers\Public\ProjectController@show');
    });

    Router::addGroup('/posts', function () {
        Router::get('/{id}', 'App\Presentation\Http\Controllers\Public\PostController@show');
    });

    Router::addGroup('/admin', function () use ($auth) {
        Router::addGroup('/users', function () use ($auth) {
            Router::get('/', 'App\Presentation\Http\Controllers\Admin\AdminUserController@index', [
                'middleware' => $auth,
                'permissions' => ['users.view'],
            ]);
            Router::post('/', 'App\Presentation\Http\Controllers\Admin\AdminUserController@store', [
                'middleware' => $auth,
                'permissions' => ['users.create'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminUserController@show', [
                'middleware' => $auth,
                'permissions' => ['users.view'],
            ]);
            Router::put('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminUserController@update', [
                'middleware' => $auth,
                'permissions' => ['users.update'],
            ]);
        });

        Router::get('/roles', 'App\Presentation\Http\Controllers\Admin\RbacController@listRoles', [
            'middleware' => $auth,
            'permissions' => ['roles.view'],
        ]);
        Router::post('/roles', 'App\Presentation\Http\Controllers\Admin\RbacController@createRole', [
            'middleware' => $auth,
            'permissions' => ['roles.create'],
        ]);
        Router::delete('/roles/{id}', 'App\Presentation\Http\Controllers\Admin\RbacController@destroyRole', [
            'middleware' => $auth,
            'permissions' => ['roles.delete'],
        ]);
        Router::put('/roles/{id}/permissions', 'App\Presentation\Http\Controllers\Admin\RbacController@syncRolePermissions', [
            'middleware' => $auth,
            'permissions' => ['roles.assign_permissions'],
        ]);
        Router::get('/permissions', 'App\Presentation\Http\Controllers\Admin\RbacController@listPermissions', [
            'middleware' => $auth,
            'permissions' => ['permissions.view'],
        ]);
        Router::put('/users/{id}/roles', 'App\Presentation\Http\Controllers\Admin\RbacController@syncUserRoles', [
            'middleware' => $auth,
            'permissions' => ['users.assign_roles'],
        ]);

        Router::addGroup('/projects', function () use ($auth) {
            Router::get('/', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@index', [
                'middleware' => $auth,
                'permissions' => ['projects.view'],
            ]);
            Router::post('/', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@store', [
                'middleware' => $auth,
                'permissions' => ['projects.create'],
            ]);
            Router::put('/reorder', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@reorder', [
                'middleware' => $auth,
                'permissions' => ['projects.update'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@show', [
                'middleware' => $auth,
                'permissions' => ['projects.view'],
            ]);
            Router::put('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@update', [
                'middleware' => $auth,
                'permissions' => ['projects.update'],
            ]);
            Router::delete('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@destroy', [
                'middleware' => $auth,
                'permissions' => ['projects.delete'],
            ]);
            Router::post('/{id}/publish', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@publish', [
                'middleware' => $auth,
                'permissions' => ['projects.publish'],
            ]);
            Router::post('/{id}/archive', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@archive', [
                'middleware' => $auth,
                'permissions' => ['projects.publish'],
            ]);
            Router::get('/{projectId}/posts', 'App\Presentation\Http\Controllers\Admin\AdminPostController@index', [
                'middleware' => $auth,
                'permissions' => ['posts.view'],
            ]);
        });

        Router::addGroup('/posts', function () use ($auth) {
            Router::post('/', 'App\Presentation\Http\Controllers\Admin\AdminPostController@store', [
                'middleware' => $auth,
                'permissions' => ['posts.create'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPostController@show', [
                'middleware' => $auth,
                'permissions' => ['posts.view'],
            ]);
            Router::put('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPostController@update', [
                'middleware' => $auth,
                'permissions' => ['posts.update'],
            ]);
            Router::delete('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPostController@destroy', [
                'middleware' => $auth,
                'permissions' => ['posts.delete'],
            ]);
            Router::post('/{id}/publish', 'App\Presentation\Http\Controllers\Admin\AdminPostController@publish', [
                'middleware' => $auth,
                'permissions' => ['posts.publish'],
            ]);
        });
    });
});
