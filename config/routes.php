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
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/api', function () {
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
});
