<?php

use Soosyze\Components\Router\RouteCollection;
use Soosyze\Components\Router\RouteGroup;

define('LOGIN_WITHS', [ 'url' => '(/[\d\w-]{10,})?' ]);

RouteCollection::setNamespace('SoosyzeCore\User\Controller\UserApi')->prefix('/api')->group(function (RouteGroup $r): void {
    $r->get('user.api.select', '/user', '@select');
});
RouteCollection::setNamespace('SoosyzeCore\User\Controller')->name('user.')->prefix('/user')->group(function (RouteGroup $r): void {
    $r->setNamespace('\Login')->group(function (RouteGroup $r): void {
        $r->get('login', '/login{url}', '@login', LOGIN_WITHS);
        $r->post('login.check', '/login{url}', '@loginCheck', LOGIN_WITHS);
        $r->get('relogin', '/relogin{url}', '@relogin', LOGIN_WITHS);
        $r->post('relogin.check', '/relogin{url}', '@reloginCheck', LOGIN_WITHS);
        $r->get('reset', '/{id}/reset/{token}', '@resetUser', [ 'id' => '\d+', 'token' => '[\w-]+' ]);
        $r->get('logout', '/logout', '@logout');
    });
    $r->setNamespace('\Register')->name('register.')->prefix('/register')->group(function (RouteGroup $r): void {
        $r->get('create', '/', '@create');
        $r->post('store', '/', '@store');
        $r->get('activate', '/{id}/activate/{token}', '@activate', [ 'id' => '\d+', 'token' => '[\w-]+' ]);
    });
    $r->setNamespace('\User')->group(function (RouteGroup $r): void {
        $r->get('account', '/account', '@account');
        $r->get('show', '/{id}', '@show')->whereDigits('id');
        $r->post('store', '/', '@store');
        $r->get('edit', '/{id}/edit', '@edit')->whereDigits('id');
        $r->put('update', '/{id}', '@update')->whereDigits('id');
        $r->get('remove', '/{id}/delete', '@remove')->whereDigits('id');
        $r->delete('delete', '/{id}', '@delete')->whereDigits('id');
    });
});
RouteCollection::setNamespace('SoosyzeCore\User\Controller')->name('user.')->prefix('/admin/user')->group(function (RouteGroup $r): void {
    $r->get('create', '/create', '\User@create');

    $r->setNamespace('\UsersManager')->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
        $r->get('filter', '/filter', '@filter');
        $r->get('filter.page', '/filter/{pageId}', '@filter', [ 'pageId' => '[1-9]\d*' ]);
    });
    $r->setNamespace('\Permission')->name('permission.')->prefix('/permission')->group(function (RouteGroup $r): void {
        $r->get('admin', '/', '@admin');
        $r->put('update', '/', '@udpate');
    });
    $r->prefix('/role')->name('role.')->group(function (RouteGroup $r): void {
        $r->setNamespace('\RoleManager')->group(function (RouteGroup $r): void {
            $r->get('admin', '/', '@admin');
            $r->patch('admin.check', '/', '@adminCheck');
        });
        $r->setNamespace('\Role')->group(function (RouteGroup $r): void {
            $r->get('create', '/create', '@create');
            $r->post('store', '/', '@store');
            $r->get('edit', '/{id}/edit', '@edit')->whereDigits('id');
            $r->put('update', '/{id}', '@update')->whereDigits('id');
            $r->get('remove', '/{id}/delete', '@remove')->whereDigits('id');
            $r->delete('delete', '/{id}', '@delete')->whereDigits('id');
        });
    });
});
