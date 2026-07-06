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

    // Public portfolio API
    Router::get('/pages/home', 'App\Presentation\Http\Controllers\Public\PageController@home');
    Router::get('/pages', 'App\Presentation\Http\Controllers\Public\PageController@index');
    Router::get('/pages/{slug}', 'App\Presentation\Http\Controllers\Public\PageController@show');
    Router::get('/block-types', 'App\Presentation\Http\Controllers\Public\BlockTypeController@index');
    Router::get('/site/settings', 'App\Presentation\Http\Controllers\Public\SiteSettingsController@show');
    Router::post('/contact', 'App\Presentation\Http\Controllers\Public\ContactController@submit');
    Router::get('/projects', 'App\Presentation\Http\Controllers\Public\ProjectController@index');
    Router::get('/projects/{slug}/related', 'App\Presentation\Http\Controllers\Public\ProjectController@related');
    Router::get('/projects/{slug}', 'App\Presentation\Http\Controllers\Public\ProjectController@show');
    Router::get('/technologies', 'App\Presentation\Http\Controllers\Public\TaxonomyController@technologies');
    Router::get('/categories', 'App\Presentation\Http\Controllers\Public\TaxonomyController@categories');
    Router::get('/tags', 'App\Presentation\Http\Controllers\Public\TaxonomyController@tags');
    Router::get('/search', 'App\Presentation\Http\Controllers\Public\ProjectController@search');

    Router::addGroup('/admin', function () use ($auth) {
        Router::addGroup('/users', function () use ($auth) {
            Router::get('/', 'App\Presentation\Http\Controllers\Admin\AdminUserController@index', [
                'middleware' => $auth, 'permissions' => ['users.view'],
            ]);
            Router::post('/', 'App\Presentation\Http\Controllers\Admin\AdminUserController@store', [
                'middleware' => $auth, 'permissions' => ['users.create'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminUserController@show', [
                'middleware' => $auth, 'permissions' => ['users.view'],
            ]);
            Router::put('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminUserController@update', [
                'middleware' => $auth, 'permissions' => ['users.update'],
            ]);
        });

        Router::get('/roles', 'App\Presentation\Http\Controllers\Admin\RbacController@listRoles', [
            'middleware' => $auth, 'permissions' => ['roles.view'],
        ]);
        Router::post('/roles', 'App\Presentation\Http\Controllers\Admin\RbacController@createRole', [
            'middleware' => $auth, 'permissions' => ['roles.create'],
        ]);
        Router::delete('/roles/{id}', 'App\Presentation\Http\Controllers\Admin\RbacController@destroyRole', [
            'middleware' => $auth, 'permissions' => ['roles.delete'],
        ]);
        Router::put('/roles/{id}/permissions', 'App\Presentation\Http\Controllers\Admin\RbacController@syncRolePermissions', [
            'middleware' => $auth, 'permissions' => ['roles.assign_permissions'],
        ]);
        Router::get('/permissions', 'App\Presentation\Http\Controllers\Admin\RbacController@listPermissions', [
            'middleware' => $auth, 'permissions' => ['permissions.view'],
        ]);
        Router::put('/users/{id}/roles', 'App\Presentation\Http\Controllers\Admin\RbacController@syncUserRoles', [
            'middleware' => $auth, 'permissions' => ['users.assign_roles'],
        ]);

        Router::post('/uploads', 'App\Presentation\Http\Controllers\Admin\AdminUploadController@store', [
            'middleware' => $auth, 'permissions' => ['uploads.create'],
        ]);

        Router::addGroup('/projects', function () use ($auth) {
            Router::get('/statistics', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@stats', [
                'middleware' => $auth, 'permissions' => ['projects.view'],
            ]);
            Router::patch('/order', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@reorder', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::get('/', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@index', [
                'middleware' => $auth, 'permissions' => ['projects.view'],
            ]);
            Router::post('/', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@store', [
                'middleware' => $auth, 'permissions' => ['projects.create'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@show', [
                'middleware' => $auth, 'permissions' => ['projects.view'],
            ]);
            Router::put('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@update', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::patch('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@patch', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::delete('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@destroy', [
                'middleware' => $auth, 'permissions' => ['projects.delete'],
            ]);
            Router::delete('/{id}/force', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@forceDestroy', [
                'middleware' => $auth, 'permissions' => ['projects.delete'],
            ]);
            Router::patch('/{id}/restore', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@restore', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::patch('/{id}/publish', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@publish', [
                'middleware' => $auth, 'permissions' => ['projects.publish'],
            ]);
            Router::patch('/{id}/archive', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@archive', [
                'middleware' => $auth, 'permissions' => ['projects.publish'],
            ]);
            Router::patch('/{id}/draft', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@draft', [
                'middleware' => $auth, 'permissions' => ['projects.publish'],
            ]);
            Router::post('/{id}/duplicate', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@duplicate', [
                'middleware' => $auth, 'permissions' => ['projects.create'],
            ]);
            Router::post('/{id}/images', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@addImage', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::delete('/{id}/images/{imageId}', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@removeImage', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::patch('/{id}/images/order', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@reorderImages', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::patch('/{id}/thumbnail', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@setThumbnail', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::patch('/{id}/cover', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@setCover', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::put('/{id}/categories', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@syncCategories', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::put('/{id}/technologies', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@syncTechnologies', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
            Router::put('/{id}/tags', 'App\Presentation\Http\Controllers\Admin\AdminProjectController@syncTags', [
                'middleware' => $auth, 'permissions' => ['projects.update'],
            ]);
        });

        Router::addGroup('/pages', function () use ($auth) {
            Router::patch('/order', 'App\Presentation\Http\Controllers\Admin\AdminPageController@reorder', [
                'middleware' => $auth, 'permissions' => ['pages.update'],
            ]);
            Router::get('/', 'App\Presentation\Http\Controllers\Admin\AdminPageController@index', [
                'middleware' => $auth, 'permissions' => ['pages.view'],
            ]);
            Router::post('/', 'App\Presentation\Http\Controllers\Admin\AdminPageController@store', [
                'middleware' => $auth, 'permissions' => ['pages.create'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPageController@show', [
                'middleware' => $auth, 'permissions' => ['pages.view'],
            ]);
            Router::put('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPageController@update', [
                'middleware' => $auth, 'permissions' => ['pages.update'],
            ]);
            Router::patch('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPageController@patch', [
                'middleware' => $auth, 'permissions' => ['pages.update'],
            ]);
            Router::delete('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminPageController@destroy', [
                'middleware' => $auth, 'permissions' => ['pages.delete'],
            ]);
            Router::delete('/{id}/force', 'App\Presentation\Http\Controllers\Admin\AdminPageController@forceDestroy', [
                'middleware' => $auth, 'permissions' => ['pages.delete'],
            ]);
            Router::patch('/{id}/restore', 'App\Presentation\Http\Controllers\Admin\AdminPageController@restore', [
                'middleware' => $auth, 'permissions' => ['pages.update'],
            ]);
            Router::patch('/{id}/publish', 'App\Presentation\Http\Controllers\Admin\AdminPageController@publish', [
                'middleware' => $auth, 'permissions' => ['pages.publish'],
            ]);
            Router::patch('/{id}/archive', 'App\Presentation\Http\Controllers\Admin\AdminPageController@archive', [
                'middleware' => $auth, 'permissions' => ['pages.publish'],
            ]);
            Router::patch('/{id}/draft', 'App\Presentation\Http\Controllers\Admin\AdminPageController@draft', [
                'middleware' => $auth, 'permissions' => ['pages.publish'],
            ]);
            Router::post('/{id}/duplicate', 'App\Presentation\Http\Controllers\Admin\AdminPageController@duplicate', [
                'middleware' => $auth, 'permissions' => ['pages.create'],
            ]);
            Router::put('/{id}/blocks', 'App\Presentation\Http\Controllers\Admin\AdminPageController@syncBlocks', [
                'middleware' => $auth, 'permissions' => ['pages.update'],
            ]);
        });

        Router::get('/site/settings', 'App\Presentation\Http\Controllers\Admin\AdminSiteSettingsController@show', [
            'middleware' => $auth, 'permissions' => ['site.update'],
        ]);
        Router::put('/site/settings', 'App\Presentation\Http\Controllers\Admin\AdminSiteSettingsController@update', [
            'middleware' => $auth, 'permissions' => ['site.update'],
        ]);

        Router::addGroup('/contact/messages', function () use ($auth) {
            Router::get('/', 'App\Presentation\Http\Controllers\Admin\AdminContactMessageController@index', [
                'middleware' => $auth, 'permissions' => ['contact.view'],
            ]);
            Router::get('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminContactMessageController@show', [
                'middleware' => $auth, 'permissions' => ['contact.view'],
            ]);
            Router::patch('/{id}', 'App\Presentation\Http\Controllers\Admin\AdminContactMessageController@update', [
                'middleware' => $auth, 'permissions' => ['contact.update'],
            ]);
        });
    });
});
