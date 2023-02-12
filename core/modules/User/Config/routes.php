<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;
use Soosyze\Core\Modules\User\Controller as Ctr;

define('LOGIN_WITHS', [ 'url' => '(/[\d\w-]{10,})?' ]);

RouteCollection::setNamespace(Ctr\UserApi::class)->prefix('/api')->group(function (RouteGroup $r): void {
    $r->get('user.api.select', '/user', '@select');
});
RouteCollection::name('user.')->prefix('/user')->group(function (RouteGroup $r): void {
    $r->setNamespace(Ctr\Login::class)->group(function (RouteGroup $r): void {
        $r->get('login', '/login{url}', '@login', LOGIN_WITHS);
        $r->post('login.check', '/login{url}', '@loginCheck', LOGIN_WITHS);
        $r->get('relogin', '/relogin{url}', '@relogin', LOGIN_WITHS);
        $r->post('relogin.check', '/relogin{url}', '@reloginCheck', LOGIN_WITHS);
        $r->get('reset', '/{id}/reset/{token}', '@resetUser', [ 'id' => '\d+', 'token' => '[\w-]+' ]);
        $r->get('logout', '/logout', '@logout');
    });
    $r->setNamespace(Ctr\Register::class)->name('register.')->prefix('/register')->group(function (RouteGroup $r): void {
        $r->get('create', '/', '@create');
        $r->post('store', '/', '@store');
        $r->get('activate', '/{id}/activate/{token}', '@activate', [ 'id' => '\d+', 'token' => '[\w-]+' ]);
    });
    $r->setNamespace(Ctr\User::class)->group(function (RouteGroup $r): void {
        $r->get('account', '/account', '@account');
        $r->get('show', '/{id}', '@show')->whereDigits('id');
        $r->post('store', '/', '@store');
        $r->get('edit', '/{id}/edit', '@edit')->whereDigits('id');
        $r->put('update', '/{id}', '@update')->whereDigits('id');
        $r->get('remove', '/{id}/delete', '@remove')->whereDigits('id');
        $r->delete('delete', '/{id}', '@delete')->whereDigits('id');
    });
});
RouteCollection::name('user.')->prefix('/admin/user')->group(function (RouteGroup $r): void {
    $r->get('create', '/create', Ctr\User::class . '@create');

    $r->setNamespace(Ctr\UsersManager::class)->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
        $r->get('filter', '/filter', '@filter');
        $r->get('filter.page', '/filter/{pageId}', '@filter', [ 'pageId' => '[1-9]\d*' ]);
    });
    $r->setNamespace(Ctr\Permission::class)->name('permission.')->prefix('/permission')->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
        $r->put('update', '/', '@udpate');
    });
    $r->prefix('/role')->name('role.')->group(function (RouteGroup $r): void {
        $r->setNamespace(Ctr\RoleManager::class)->group(function (RouteGroup $r): void {
            $r->get('admin', '/', '@admin');
            $r->patch('admin.check', '/', '@adminCheck');
        });
        $r->setNamespace(Ctr\Role::class)->group(function (RouteGroup $r): void {
            $r->get('create', '/create', '@create');
            $r->post('store', '/', '@store');
            $r->get('edit', '/{id}/edit', '@edit')->whereDigits('id');
            $r->put('update', '/{id}', '@update')->whereDigits('id');
            $r->get('remove', '/{id}/delete', '@remove')->whereDigits('id');
            $r->delete('delete', '/{id}', '@delete')->whereDigits('id');
        });
    });
});
